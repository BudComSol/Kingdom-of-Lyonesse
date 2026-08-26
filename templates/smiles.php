<script type="text/javascript">
function tag(id, html) {
    var e = document.getElementById(id);
    if (e != null) {
        e.value += ' ' + html + ' ';
        e.focus();
    }
}
function showSmilies() {
    var e = document.getElementById('smiles');
    if (e != null) {
        e.style.display = (e.style.display == 'block') ? 'none' : 'block';
    }
}
</script>
<div class="smile" id="smiles" style="display: none;">
    <center>
        <?php for ($i = 0; $i <= 99; $i++) { ?>
            <a href="javascript:tag('message','[smile<?php echo $i; ?>]')"><img src="/images/emoji/<?php echo $i; ?>.png" alt="emoji"></a>
        <?php } ?>
        <div class="hrs"></div>
    </center>
</div>