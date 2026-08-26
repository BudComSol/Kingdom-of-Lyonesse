<?php
$go = number($_GET['go'] ?? 0);
$step = (int)($user['start'] ?? 4);

if ($go == 1 && $step != 7) {
    qry("UPDATE `users` SET `start` = `start` + ? WHERE `id` = ? LIMIT 1", array(1, $uid));
    header('Location: /');
    exit();
}

$sex = (($user['sex'] ?? '1') == '1') ? 'Male' : 'Female';

if (isset($_GET['sex'])) {
    $gsex = (($user['sex'] ?? '1') == '1') ? '2' : '1';
    qry("UPDATE `users` SET `sex` = ? WHERE `id` = ? LIMIT 1", array($gsex, $uid));
    header('Location: /');
    exit();
}

// Hero Name Creation Step
if ($step == 7) {
    if (isset($_GET['form'])) {
        $loginInput = checking($_POST['login'] ?? '');
        $err = false;

        if (mb_strlen($loginInput) < 3 || mb_strlen($loginInput) > 20) {
            err2("Hero name must be between 3 and 20 characters");
            $err = true;
        } else {
            $search1 = fch("SELECT * FROM `users` WHERE `login` = ? AND `id` != ? LIMIT 1", array($loginInput, $uid));
            if ($search1) {
                err2("Hero name is already taken");
                $err = true;
            }
        }

        if ($err === false) {
            $rou = rand(100000, 999999);
            $hash = md5(md5((string)$rou));
            qry("UPDATE `users` SET `login` = ?, `save` = ?, `start` = `start` + ? WHERE `id` = ? LIMIT 1", array($loginInput, 1, 1, $uid));
            header("Location: /");
            exit();
        }

        header("Location: /");
        exit();
    }
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
        <small>You're awake! That's wonderful. After the battle in the woods you were severely wounded. Many feared you would perish before giving us any answers.</small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>What happened? Where am I?</small>
    </div>

<?php if ($step >= 5) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>Lately, something terrifying has been attacking our travelers in the Dark Forest. Nobody survived before... until you.</small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>Everything is a blur... I barely remember anything.</small>
    </div>
<?php } ?>

<?php if ($step >= 6) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>Let us start simple then. Do you remember your name, brave traveler?</small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>My name is...</small>
    </div>
<?php } ?>

<?php
} else {
?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>Splendid, <?php echo htmlspecialchars($user['login'] ?? 'Knight'); ?>! Let me show you how things work around our kingdom while your memories recover.</small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>I appreciate your kindness. I am listening.</small>
    </div>

<?php if ($step >= 9) { ?>
    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>
            You are in the Kingdom of <span class="yellow">Lyonesse</span>. You can earn coin and rest at the local Tavern.<br><br>
            In the Craftsmen District, you will find the Blacksmith, who can forge powerful weapons and armor for you.<br><br>
            To gather crafting materials, explore the Dark Forest and defeat fierce monsters, or trade resources in the Marketplace.<br><br>
            To grow stronger, train your combat abilities and equip them before entering battle!
        </small>
    </div>

    <div class="tophead">
        <span class="tomato">You</span><br>
        <small>Thank you, Elaine. I will not forget your help.</small>
    </div>

    <div class="tophead">
        <div class="ohh"><img src="/images/start/girl.png" width="50px" height="50px" alt="Elaine"></div>
        <span class="lime">Elaine</span><br>
        <small>You're welcome! Now go meet <span class="yellow">Jack</span> at the Tavern to begin your journey.</small>
    </div>
    <br>
<?php } ?>

<?php } ?>

<div class="tophead2 cntr">
    <div class="hrs"></div>
    <a href="?go=1" class="button">Continue</a>
    <div class="hrs"></div>
</div>