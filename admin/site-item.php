<div class="site-item">
    <div class="col">
        <div class="domain"><?php echo $site->domain ?></div>
    </div>
    <div class="col">
        <div class="token"><?php echo $site->token ?></div>
    </div>
    <div class="action">
        <label class="switch">
            <input type="checkbox" class="site-status" data-site="<?php echo $site->id ?>" value="<?php echo $site->id ?>" <?php echo $site->status == 'active' ? 'checked' : '' ?>>
            <span class="slider round"></span>
        </label>
    </div>
</div>