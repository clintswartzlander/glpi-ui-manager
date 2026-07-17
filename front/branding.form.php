<?php

declare(strict_types=1);

use GlpiPlugin\Uimanager\Branding\BrandingController;

include '../../../inc/includes.php';
require_once dirname(__DIR__) . '/inc/autoload.php';
BrandingController::authorize();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); header('Allow: POST'); exit; }
try {
    $entityId = BrandingController::process($_POST, $_FILES);
    Session::addMessageAfterRedirect(__('Branding settings saved.', 'uimanager'), true, INFO);
} catch (InvalidArgumentException | RuntimeException $exception) {
    $entityId = max(0, (int) ($_POST['entities_id'] ?? 0));
    Session::addMessageAfterRedirect($exception->getMessage(), true, ERROR);
}
Html::redirect($CFG_GLPI['root_doc'] . '/plugins/uimanager/front/branding.php?entities_id=' . $entityId);
