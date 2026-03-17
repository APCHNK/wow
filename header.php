<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Parisienne&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
    $header_logo = get_field('header_logo', 'option') ?: get_template_directory_uri() . '/assets/images/logo.svg';
    $header_nav_items = get_field('header_nav_items', 'option');
    $header_phone = get_field('header_phone', 'option') ?: '+48571286783';
    $header_email = get_field('header_email', 'option') ?: 'event@golden5here.com';
    $header_instagram = get_field('header_instagram', 'option') ?: '#';
    $header_facebook = get_field('header_facebook', 'option') ?: '#';
?>

<header class="site-header">
    <div class="header-container">
        <button class="menu-toggle" id="menu-toggle">
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="menu-text"><span class="menu-text-default">MENU</span></span>
        </button>

        <div class="site-logo">
            <a href="<?php echo home_url(); ?>">
                <img src="<?php echo esc_url($header_logo); ?>" alt="<?php bloginfo('name'); ?>" width="104" height="93">
            </a>
        </div>

        <div class="header-spacer"></div>
    </div>
</header>

<nav class="main-nav" id="main-nav">
    <div class="nav-content">
        <?php if (!empty($header_nav_items)) : ?>
            <?php
            $columns = array_chunk($header_nav_items, 2);
            foreach ($columns as $column) : ?>
            <div class="nav-column">
                <?php foreach ($column as $item) : ?>
                <div class="nav-item">
                    <?php if (!empty($item['link'])) : ?>
                    <a href="<?php echo esc_url($item['link']); ?>">
                        <h3 class="nav-title"><?php echo esc_html($item['title']); ?></h3>
                    </a>
                    <?php else : ?>
                    <h3 class="nav-title"><?php echo esc_html($item['title']); ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($item['description'])) : ?>
                    <p class="nav-desc"><?php echo esc_html($item['description']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        <?php else : ?>
        <div class="nav-column">
            <div class="nav-item">
                <h3 class="nav-title">WHAT WE SPECIALISE IN</h3>
                <p class="nav-desc">We create weddings, proposals, engagements, and gender reveal parties — heartfelt celebrations that reflect your love and your story.</p>
            </div>
            <div class="nav-item">
                <h3 class="nav-title">WHO WE ARE</h3>
                <p class="nav-desc">Anniversaries, birthdays, Bar and Bat Mitzvahs, and other meaningful family events — planned with care, respect for tradition, and attention to detail.</p>
            </div>
        </div>
        <div class="nav-column">
            <div class="nav-item">
                <h3 class="nav-title">MAKE IT HAPPEN</h3>
                <p class="nav-desc">Intimate celebrations for friends and loved ones — warm, stylish, and tailored to your occasion.</p>
            </div>
            <div class="nav-item">
                <h3 class="nav-title">FOLLOW US ON INSTAGRAM</h3>
                <p class="nav-desc">Corporate parties, team buildings, presentations, conference after-parties, and client receptions — professionally organized with the right atmosphere and flow.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="nav-footer">
        <div class="nav-contacts">
            <div class="contact-item">
                <span class="contact-label">Contacts</span>
                <a href="tel:<?php echo esc_attr($header_phone); ?>" class="contact-value"><?php echo esc_html($header_phone); ?></a>
            </div>
            <div class="contact-item">
                <span class="contact-label">E-mail</span>
                <a href="mailto:<?php echo esc_attr($header_email); ?>" class="contact-value"><?php echo esc_html($header_email); ?></a>
            </div>
            <div class="contact-item">
                <a href="/faq" class="contact-label">FAQ</a>
            </div>
        </div>
        <div class="nav-social">
            <a href="<?php echo esc_url($header_instagram); ?>" class="social-link" aria-label="Instagram">
                <svg width="78" height="78" viewBox="0 0 78 78" fill="none" xmlns="http://www.w3.org/2000/svg">
<g id="inst" opacity="0.801386">
<path id="Shape" fill-rule="evenodd" clip-rule="evenodd" d="M24.375 0H53.625C67.0849 0 78 10.9151 78 24.375V53.625C78 67.0849 67.0849 78 53.625 78H24.375C10.9151 78 0 67.0849 0 53.625V24.375C0 10.9151 10.9151 0 24.375 0ZM53.625 70.6875C63.0337 70.6875 70.6875 63.0337 70.6875 53.625V24.375C70.6875 14.9662 63.0337 7.3125 53.625 7.3125H24.375C14.9662 7.3125 7.3125 14.9662 7.3125 24.375V53.625C7.3125 63.0337 14.9662 70.6875 24.375 70.6875H53.625Z" fill="white"/>
<path id="Shape_2" fill-rule="evenodd" clip-rule="evenodd" d="M19.5 39C19.5 28.2311 28.2311 19.5 39 19.5C49.7689 19.5 58.5 28.2311 58.5 39C58.5 49.7689 49.7689 58.5 39 58.5C28.2311 58.5 19.5 49.7689 19.5 39ZM26.8125 39C26.8125 45.7178 32.2822 51.1875 39 51.1875C45.7178 51.1875 51.1875 45.7178 51.1875 39C51.1875 32.2774 45.7178 26.8125 39 26.8125C32.2822 26.8125 26.8125 32.2774 26.8125 39Z" fill="white"/>
<path id="Oval" d="M59.9617 20.6362C61.3967 20.6362 62.56 19.4729 62.56 18.0378C62.56 16.6028 61.3967 15.4395 59.9617 15.4395C58.5266 15.4395 57.3633 16.6028 57.3633 18.0378C57.3633 19.4729 58.5266 20.6362 59.9617 20.6362Z" fill="white"/>
</g>
</svg>

            </a>
            <a href="<?php echo esc_url($header_facebook); ?>" class="social-link" aria-label="Facebook">
                <svg width="78" height="78" viewBox="0 0 78 78" fill="none" xmlns="http://www.w3.org/2000/svg">
<g id="face" opacity="0.801386">
<path id="Shape" fill-rule="evenodd" clip-rule="evenodd" d="M0 39C0 17.46 17.46 0 39 0C60.54 0 78 17.46 78 39C78 60.54 60.54 78 39 78C17.46 78 0 60.54 0 39ZM6.09375 39C6.09375 57.1742 20.8258 71.9062 39 71.9062C57.1742 71.9062 71.9062 57.1742 71.9062 39C71.9062 20.8258 57.1742 6.09375 39 6.09375C20.8258 6.09375 6.09375 20.8258 6.09375 39Z" fill="white"/>
<path id="Path" d="M48.252 33.03H41.4679V28.0788C41.4679 26.5601 43.0389 26.2078 43.7721 26.2078C44.5005 26.2078 48.1546 26.2078 48.1546 26.2078V19.5262L43.1318 19.5C36.279 19.5 34.7148 24.4821 34.7148 27.6766V33.0301H29.7422V39.9141H34.7148C34.7148 48.7524 34.7148 58.5 34.7148 58.5H41.4679C41.4679 58.5 41.4679 48.6523 41.4679 39.9141H47.2046L48.252 33.03Z" fill="white"/>
</g>
</svg>

            </a>
        </div>
    </div>
</nav>
