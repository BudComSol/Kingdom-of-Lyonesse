<?php
$step = (int)($user['start'] ?? 4);

$sex = (($user['sex'] ?? '1') == '1') ? 'Male' : 'Female';

// Hero Name Creation Step
if ($step == 7) {
?>
    <div class="tophead cntr">Character Gender: <?php echo $sex; ?> - <a href="?sex">[Change]</a></div>
    <div class="tophead cntr">
        <?php echo logs(); ?>
        <form method="post" action="/index.php?form">
            Hero Name:<br>
            <input type="text" name="login" value="<?php echo htmlspecialchars($user['login'] ?? ''); ?>" required minlength="3" maxlength="20">
            <br><br>
            <input type="submit" value="Continue">
        </form>
    </div>
<?php
    foot($uid);
    exit();
}

if ($step <= 6) {
?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small><p>You're awake, that's wonderful.</p><p>After the battle in the woods you were severely wounded, many feared you would perish before giving us any answers.</p></small>
    </div>t

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small><p>What happened, where in heavens name am I?</p></small>
    </div>

<?php if ($step >= 5) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small><p>Lately, something utterly terrifying has been attacking our travelers in the DARK FOREST, nobody had survived before, at least until you.</p></small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small><p>Everything is a blur... I barely remember anything at all.</p></small>
    </div>
<?php } ?>

<?php if ($step >= 6) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small><p>Let us start with something simple then, do you remember your name, brave traveler?</p></small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small><p> Umm, my name is... (can be changed on the next page)</p></small>
    </div>
<?php } ?>

<?php
} else {
?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small><p>Splendid <?php echo htmlspecialchars($user['login'] ?? 'Knight'); ?> ! Let me show you how things work around our kingdom while your memories recover.</p></small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small><p>Thank you I appreciate your kindness, please go on, I am listening.</p></small>
    </div>

<?php if ($step >= 9) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>
            <p>You are in the <span class="yellow"> Kingdom of Lyonesse.</p><p>You can earn coin and take rest at the local Tavern.</p>
            <p>In the Craftsmen District, you will find the blacksmith, who can forge powerful weapons and armor for you.</p>
            <p>To gather crafting materials, explore the Dark Forest and defeat fierce monsters, or trade resources in the Marketplace.</p>
            <p>To grow stronger, train your combat abilities and equip them before entering into battle!</p>
        </small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small><p>Thank you Elaine, I will not forget your help and the kindness you have shown me.</p></small>
    </div>

    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small><p>You're welcome, now go meet <span class="yellow">Jack</span> at the Tavern to begin your perilous journey.</p></small>
    </div>
    <br>
<?php } ?>

<?php } ?>

<div class="tophead2 cntr">
    <div class="hrs"></div>
    <a href="?go=1" class="button">Continue</a>
    <div class="hrs"></div>
</div>
