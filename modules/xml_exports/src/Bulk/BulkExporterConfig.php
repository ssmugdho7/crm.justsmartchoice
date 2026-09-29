<?php

namespace Techy4m\XmlExports\Bulk;

class BulkExporterConfig
{
    private string $status;
    private string $directory;
    private string $dateFrom;
    private string $dateTo;
    private bool $hasPermission;
    private string $exportType;
    private string $dateColumn;

    public function __construct(
        string $exportType,
        bool   $hasPermission,
        string $dateFrom,
        string $dateTo,
        string $status,
        string $dateColumn = 'date'
    )
    {
        $this->exportType = $exportType;
        $this->status = $status;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->hasPermission = $hasPermission;

        $this->directory = TEMP_FOLDER . $this->exportType;
        $this->dateColumn = $dateColumn;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function hasPermission(): bool
    {
        return $this->hasPermission;
    }

    public function getDateColumn(): string
    {
        return $this->dateColumn;
    }

    public function getDateFrom(): string
    {
        return $this->dateFrom;
    }

    public function getDateTo(): string
    {
        return $this->dateTo;
    }

    public function getDir(): string
    {
        return $this->directory;
    }

    public function getErrorRedirectURL(): string
    {
        return admin_url('xml_exports/bulk_export');
    }
}
