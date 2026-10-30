<?php

namespace app\services\messages;

defined('BASEPATH') or exit('No direct script access allowed');

abstract class AbstractMessage
{
    protected $alertClass = 'danger';

    abstract public function isVisible();

    abstract public function getMessage();

    public function openHtml()
    {
        $source = in_array(static::class, [CronJobFailure::class, IsCronSetupRequired::class], true) ? 'cron' : 'alertFloat';
        return '<div class="col-md-12 tw-mt-1">
            <div class="alert alert-' . $this->alertClass . '" data-toast-source="' . $source . '" font-medium>';
    }

    public function closeHtml()
    {
        return '</div>
			</div>';
    }

    public static function fqcn()
    {
        return self::class;
    }
}
