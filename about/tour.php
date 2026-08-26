<?php
$location = true;
require_once __DIR__ . "/../core/func.php";

head("Royal Tournament Guide", $uid);
?>

<div class="tophead2 cntr">
    <small>
        <font color="#888">
            The Royal Tournament is a weekly competitive championship where all warriors and knights can compete for glory and royal rewards!<br><br>
            Tournament points (Trophies) are earned by completing daily quests, emerging victorious in the PvP Arena, and defeating ferocious beasts in the Dark Forest.<br><br>
            <b>Weekly Tournament Rewards:</b><br>
            Top 10 leaderboard champions will receive a large payout of <img src="/images/ruby.png" width="17px" height="17px" alt="Ruby"> Rubies based on their trophy count!<br>
            Every warrior who gathers 250 or more trophies during the week will also receive a <b>50 Ruby</b> prize package!
        </font>
        <br><br>
        Tournament standings reset automatically every Sunday at 00:00 UTC.
    </small>
</div>

<a href="/tour.php" class="tophead">Back to Tournament Standings</a>

<?php
foot($uid);
?>