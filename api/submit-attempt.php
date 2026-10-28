<?php declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

$scenarioId = filter_input(
    INPUT_POST,
    'scenario_id',
    FILTER_VALIDATE_INT
);

$response = trim((string) ($_POST['response'] ?? ''));

if (!$scenarioId || $scenarioId < 1) {
    http_response_code(400);
    exit('Invalid scenario selected.');
}

if (mb_strlen($response) < 10 || mb_strlen($response) > 5000) {
    http_response_code(400);
    exit('Your response must contain between 10 and 5000 characters.');
}

try {
    $scenarioStatement = $pdo->prepare(
        'SELECT id FROM scenarios WHERE id = :id AND is_active = 1'
    );
    $scenarioStatement->execute(['id' => $scenarioId]);

    if (!$scenarioStatement->fetch()) {
        http_response_code(404);
        exit('This scenario is unavailable.');
    }

    $criteriaStatement = $pdo->prepare(
        'SELECT id, name, description, weight, rule_type, rule_value
         FROM criteria
         WHERE scenario_id = :scenario_id
         ORDER BY id ASC'
    );
    $criteriaStatement->execute(['scenario_id' => $scenarioId]);
    $criteria = $criteriaStatement->fetchAll();

    if (!$criteria) {
        http_response_code(422);
        exit('This scenario has no evaluation criteria yet.');
    }

    $normalizedResponse = mb_strtolower($response, 'UTF-8');
    $totalWeight = 0.0;
    $weightedScore = 0.0;
    $evaluations = [];

    foreach ($criteria as $criterion) {
        $weight = max(0.0, (float) $criterion['weight']);
        $totalWeight += $weight;

        $keywords = array_filter(
            array_map(
                static fn(string $word): string => trim(mb_strtolower($word, 'UTF-8')),
                explode(';', (string) $criterion['rule_value'])
            ),
            static fn(string $word): bool => $word !== ''
        );

        $matchedKeywords = [];

        if (
            strtolower((string) $criterion['rule_type']) === 'keyword'
            && count($keywords) > 0
        ) {
            foreach ($keywords as $keyword) {
                if (mb_strpos($normalizedResponse, $keyword) !== false) {
                    $matchedKeywords[] = $keyword;
                }
            }

            $criterionScore = round(
                count($matchedKeywords) / count($keywords) * 100,
                2
            );
        } else {
            $criterionScore = 0.0;
        }

        $weightedScore += $criterionScore * $weight;

        $feedback = count($matchedKeywords) > 0
            ? 'Matched terms: ' . implode(', ', $matchedKeywords) . '. Review whether your response addresses the criteria fully.'
            : 'No configured keywords were detected. Review this criteria and consider addressing it more explicitly.';

        $evaluations[] = [
            'criterion_id' => (int) $criterion['id'],
            'name' => (string) $criterion['name'],
            'score' => $criterionScore,
            'feedback' => $feedback,
        ];
    }

    if ($totalWeight <= 0) {
        http_response_code(422);
        exit('The evaluation criteria have no valid weights.');
    }

    $totalScore = round($weightedScore / $totalWeight, 2);

    $pdo->beginTransaction();

    $attemptStatement = $pdo->prepare(
        'INSERT INTO attempts (scenario_id, response_text, total_score, created_at)
         VALUES (:scenario_id, :response_text, :total_score, NOW())'
    );

    $attemptStatement->execute([
        'scenario_id' => $scenarioId,
        'response_text' => $response,
        'total_score' => $totalScore,
    ]);

    $attemptId = (int) $pdo->lastInsertId();

    $scoreStatement = $pdo->prepare(
        'INSERT INTO attempt_scores (attempt_id, criterion_id, score, feedback)
         VALUES (:attempt_id, :criterion_id, :score, :feedback)'
    );

    foreach ($evaluations as $evaluation) {
        $scoreStatement->execute([
            'attempt_id' => $attemptId,
            'criterion_id' => $evaluation['criterion_id'],
            'score' => $evaluation['score'],
            'feedback' => $evaluation['feedback'],
        ]);
    }

    $pdo->commit();

    header('Location: ../results.php?id=' . $attemptId);
    exit;
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('ServiceReady submission failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('We could not save your assessment. Check the PHP error log.');
}
