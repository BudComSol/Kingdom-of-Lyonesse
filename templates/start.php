<?php
qry("UPDATE `users` SET `news` = ?, `chat_check` = ? WHERE `id` = ? LIMIT 1", array(1, 1, $uid));

$title = array(
    '',
    'First Memory',
    'First Encounter',
    'Battle with the Dragon',
    ''
);

$text = array(
    '',
    '<p>Waking up in a dark forest, you cannot comprehend what happened.</p><p>With blood on your hands panic sets in, but distant cries force you into action.</p><p>SHIT & CORRUPTION ... WHAT AM I GOING TO DO?</p>',
    '<p>Rushing towards the cries, you witness a fierce battle</p><p> The fiery enemy holds overwhelming strength and power, and in a flash a brave knight falls bleeding to the cold hard ground.</p><p>The dragon turns its blazing gaze toward you and lunges.</p><p style="color: red;"> YOU HAVE NO CHOICE BUT TO FIGHT FOR YOUR VERY LIFE!</p>',
    '<P>After a brief clash, you realize you stand no chance against the beast and struck by devastating blows, you collapse into darkness.</p><p  style="color: red;">THE LAST THING YOU SEE IS THE ROARING DRAGON AND ITS DEVASTATINGLY SCORCHING FLAMES BEFORE YOU BLACKOUT!</P>',
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
