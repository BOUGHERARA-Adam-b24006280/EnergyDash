<?php
namespace Blog\Views;
class Homepage {
    public function show(): void {
        ob_start();
        ?><h1>Les derniers billets du blog</h1>
        <?php foreach ($this->posts as $post) { ?>
            <div class="news">
                <h3><?= htmlspecialchars($post->getTitle()); ?><em>: <?= $post->getDate(); ?></em></h3>
                <p>
                    <?= nl2br(htmlspecialchars($post->getContent())); ?><br>
                    <em><a href="<?= BASE_URL ?>/index.php?action=post&id=<?= urlencode($post->getId()) ?>">+</a></em>
                </p>
            </div>
            <?php
        }
        (new \Blog\Views\Layout('Le meilleur blog', ob_get_clean()))->show();
    }
}