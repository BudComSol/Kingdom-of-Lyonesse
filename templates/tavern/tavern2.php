<div class="tophead">
    <div class="ohh">
        <img class="img4" src="/images/tavern/nps1.png" width="50px" height="50px" alt="Jack">
    </div>
    <span class="lime">Jack</span><br>
    <small>Don't keep me waiting! Go see <span class="yellow">Luther</span> at the Town Market Square!</small>
</div>
<br>

<?php if (($tavern['id_quest'] ?? 0) == 1) { ?>
    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <a href="/shop.php?loc=1" class="button">Visit Luther (Equipment)</a>
        <div class="hrs"></div>
    </div>
<?php } ?>

<?php if (($tavern['id_quest'] ?? 0) == 2) { ?>
    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>Done! Luther forged me a solid piece of gear!</small>
    </div>

    <div class="tophead">
        <div class="ohh">
            <img class="img4" src="/images/tavern/nps1.png" width="50px" height="50px" alt="Jack">
        </div>
        <span class="lime">Jack</span><br>
        <small>
            Indeed, he is a gifted smith. But raw equipment isn't enough; you must forge and upgrade it!<br><br>
            Take this stone: <img src="/images/kamen.png" width="17px" height="17px" alt="Stone"> 5 Stone.<br>
            Go see <span class="yellow">Edward</span> the Blacksmith at the Town Market Square to forge and strengthen your gear!
        </small>
    </div>
    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <a href="?go=2" class="button">Claim Stone</a>
        <div class="hrs"></div>
    </div>
<?php } ?>