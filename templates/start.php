<?php
qry("UPDATE `users` SET `news` = ?, `chat_check` = ? WHERE `id` = ? LIMIT 1", array(1, 1, $uid));

$go = number($_GET['go'] ?? 0);
if ($go == 1) {
    qry("UPDATE `users` SET `start` = `start` + ? WHERE `id` = ? LIMIT 1", array(1, $uid));
    header('Location: /');
    exit();
}

$title = array(
    '',
    'First Memory',
    'First Encounter',
    'Battle with the Dragon',
    ''
);

$text = array(
    '',
    'Waking up in a dark forest, you cannot comprehend what happened. <br>Seeing blood on your hands, panic sets in, but distant cries for help force you into action.',
    'Rushing towards the cries, you witness a fierce battle. The enemy holds overwhelming power, and in moments a brave knight falls bleeding to the ground.<br><br><span class="red">The dragon turns its blazing gaze toward you and lunges! You have no choice but to fight for your life!</span>',
    'After a brief clash, you realize you stand no chance against the beast. Struck by devastating blows, you collapse into darkness.<br>The last thing you see is the roaring dragon and blazing flames...',
    ''
);

$button = array(
    '',
    'Rush to Help',
    'Fight!',
    'Continue',
    ''
);

$step = (int)($user['start'] ?? 1);
?>
<div class="tophead cntr"><span class="orange"><font size="3"><?php echo $title[$step] ?? 'Prologue'; ?></font></span></div>
<img style="top: 0" width="100%" src="/images/start/<?php echo $step; ?>.png" alt="Scene" />
<div class="tophead cntr">
    <span><?php echo $text[$step] ?? ''; ?></span>
    <center>
        <br>
        <a href="?go=1" class="button"><?php echo $button[$step] ?? 'Continue'; ?></a>
        <br>
    </center>
</div>
<br>
