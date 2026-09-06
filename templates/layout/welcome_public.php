<?php
/**
 * Standalone public layout used by the welcome / landing page.
 * The page is responsible for its own full <html>...</html> markup
 * (see templates/Pages/home.php) so we just emit the rendered content.
 *
 * @var \App\View\AppView $this
 */
echo $this->fetch('content');
