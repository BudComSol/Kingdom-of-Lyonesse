<?php
$location = true;
require_once __DIR__ . "/../core/func.php";

head("Royal Tournament Guide", $uid);
?>

<div class="tophead2 cntr">
    <small>
        <font color="#888">
            <p>The Royal Tournament is a weekly competitive championship where all warriors and knights can compete for glory and royal rewards!</p>
            <p>Tournament points (Trophies) are earned by completing daily quests, emerging victorious in the PvP Arena, and defeating ferocious beasts in the Dark Forest.</p>
            <p><b>Weekly Tournament Rewards:</b></p>
            <p>Top 10 leaderboard champions will receive a large payout of <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> Rubies based on their trophy count!</p>
            <p>Every warrior who gathers 250 or more trophies during the week will also receive a <b>50 Ruby</b> prize package!</p>
        </font>
        <p>Tournament standings reset automatically every Sunday at 00:00 UTC.</p>
    </small>
</div>


<a href="/tour.php" class="tophead cntr">← Back to Tournament Standings</a>

<?php
foot($uid);
?>
