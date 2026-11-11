<?php

function arenaDamage($attacker, $defender)
{
    $minimum = max(1, (int)floor($attacker['power'] * 0.20));
    $maximum = max($minimum, (int)ceil($attacker['power'] * 0.35));
    $damage = max(1, random_int($minimum, $maximum) - (int)floor($defender['block'] * 0.08));
    $criticalChance = 15 + (int)($attacker['critical_chance'] ?? 0);
    $critical = random_int(1, 100) <= $criticalChance;
    return [$critical ? $damage * 2 : $damage, $critical];
}

function resolveArenaDuel($challenger, $opponent)
{
    $challengerHp = max(1, (int)$challenger['health']);
    $opponentHp = max(1, (int)$opponent['health']);
    $log = [];
    $firstAttacks = random_int(0, 1) === 0;

    for ($round = 1; $round <= 40 && $challengerHp > 0 && $opponentHp > 0; $round++) {
        $order = $firstAttacks ? [0, 1] : [1, 0];
        foreach ($order as $attackerIndex) {
            if ($challengerHp <= 0 || $opponentHp <= 0) {
                break;
            }

            if ($attackerIndex === 0) {
                [$damage, $critical] = arenaDamage($challenger, $opponent);
                $opponentHp = max(0, $opponentHp - $damage);
                $log[] = "Round {$round}: Challenger deals {$damage}" . ($critical ? " critical" : "") . " damage.";
            } else {
                [$damage, $critical] = arenaDamage($opponent, $challenger);
                $challengerHp = max(0, $challengerHp - $damage);
                $log[] = "Round {$round}: Opponent deals {$damage}" . ($critical ? " critical" : "") . " damage.";
            }
        }
    }

    $challengerRatio = $challengerHp / max(1, (int)$challenger['health']);
    $opponentRatio = $opponentHp / max(1, (int)$opponent['health']);
    $winner = $challengerRatio === $opponentRatio
        ? 0
        : ($challengerRatio > $opponentRatio ? 1 : 2);

    return [
        'winner' => $winner,
        'challenger_hp' => $challengerHp,
        'opponent_hp' => $opponentHp,
        'log' => implode("\n", $log),
    ];
}
