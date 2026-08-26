<div class="tophead">
    <div class="ohh">
        <img class="img4" src="/images/tavern/nps1.png" width="50px" height="50px" alt="Jack">
    </div>
    <span class="lime">Jack</span><br>
    <small>Why are you still standing around? Go see <span class="yellow">Edward</span> at the Town Market Square!</small>
</div>
<br>

<?php if (($tavern['id_quest'] ?? 0) == 2) { ?>
    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <a href="/shop.php?loc=2" class="button">Visit Edward (Blacksmith)</a>
        <div class="hrs"></div>
    </div>
<?php } ?>

<?php if (($tavern['id_quest'] ?? 0) == 3) { ?>
    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>Edward upgraded and strengthened my equipment!</small>
    </div>

    <div class="tophead">
        <div class="ohh">
            <img class="img4" src="/images/tavern/nps1.png" width="50px" height="50px" alt="Jack">
        </div>
        <span class="lime">Jack</span><br>
        <small>
            Excellent! Now you are ready to test yourself in combat. Duel my tavern Squire—if you defeat him, I'll have lucrative contracts waiting for you!
        </small>
    </div>
    <div class="tophead2 cntr">
        <div class="hrs"></div>
        <a href="?go=3" class="button">Duel Squire!</a>
        <div class="hrs"></div>
    </div>
<?php } ?>