<?= self::enter(); ?>
<main>
  <?php if ($page->exist): ?>
    <article id="page:<?= eat($page->id); ?>">
      <h2>
        <?= $page->title; ?>
      </h2>
      <?php if ($site->has('page') && $site->has('parent')): ?>
        <p>
          <time datetime="<?= eat($page->time->format('c')); ?>">
            <?= $page->time('%A, %B %d, %Y'); ?>
          </time>
        </p>
      <?php endif; ?>
      <?= $page->content; ?>
      <?php if ($link = $page->link): ?>
        <p>
          <a href="<?= eat($link); ?>" rel="nofollow" target="_blank">
            <?= i('Link'); ?> &#x21e2;
          </a>
        </p>
      <?php endif; ?>
    </article>
  <?php else: ?>
    <article id="page:0">
      <h2>
        <?= i('Error'); ?>
      </h2>
      <p role="status">
        <?= i('%s does not exist.', 'Page'); ?>
      </p>
    </article>
  <?php endif; ?>
</main>
<?= self::exit(); ?>