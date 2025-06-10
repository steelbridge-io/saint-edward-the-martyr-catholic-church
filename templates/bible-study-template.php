<?php
/**
 * Template Name: Bible Study
 */

// Get the Twenty Seventeen header
get_header();
?>

<div class="wrap">
    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            
            <?php
            // Start the loop
            while (have_posts()) : the_post();
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>
                
                <div class="entry-content">
                    <?php
                    // Custom content can go here
                    echo '<div class="custom-template-content">This is content from our custom template!</div>';
                    
                    // Regular content
                    the_content();
                    ?>
                </div>
            </article>

             <?php
             // Add comments section
             if (comments_open() || get_comments_number()) {
              comments_template();
             }

            endwhile;
            ?>

        </main>
    </div>
</div>

<?php
get_footer();