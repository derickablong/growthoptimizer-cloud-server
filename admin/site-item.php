<div class="site-item">
    <div class="col">
        <div class="domain"><?php echo $site->domain ?></div>
    </div>
    <div class="col">
        <div class="token" title="Click to copy"><?php echo $site->token ?></div>
    </div>
    <div class="action">
        <label class="switch">
            <input type="checkbox" class="site-status" data-site="<?php echo $site->id ?>" value="<?php echo $site->id ?>" <?php echo $site->status == 'active' ? 'checked' : '' ?>>
            <span class="slider round"></span>
        </label>
        <a href="#" class="trash-item" data-site="<?php echo $site->id ?>" data-domain="<?php echo $site->domain ?>">
            <svg data-name="Layer 1" id="Layer_1" viewBox="0 0 200 200" width="30" xmlns="http://www.w3.org/2000/svg"><title/><path d="M114,100l49-49a9.9,9.9,0,0,0-14-14L100,86,51,37A9.9,9.9,0,0,0,37,51l49,49L37,149a9.9,9.9,0,0,0,14,14l49-49,49,49a9.9,9.9,0,0,0,14-14Z"/></svg>
        </a>
    </div>
</div>