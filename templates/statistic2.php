<script type="text/javascript">
function tag(id, html) {
    var e = document.getElementById(id);
    if (e != null) {
        e.value += ' ' + html + ' ';
        e.focus();
    }
}
function showSmilies2() {
    var e = document.getElementById('smiles2');
    if (e != null) {
        e.style.display = (e.style.display == 'block') ? 'none' : 'block';
    }
}
</script>
<div class="smile" id="smiles2" style="display: none;">
    <div class="hrs"></div>
    <small>
        <?php if (($users['premium_time'] ?? 0) > time()) { ?>
            <img src="/images/vip.png" width="17px" height="17px" alt="VIP"> <a href="/premium.php"><span class="orange"><u>VIP Account Active</u></span></a> - <?php echo timer($users['premium_time']); ?><br>
        <?php } ?>
    </small>
</div>