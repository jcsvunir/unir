<?php


namespace Billing;


use Ramsey\Uuid\Uuid;

class Issue
{

    private string $id;
    private string $type;
    private ?string $description = null;
    private string $severity = 'INFO';
    public function __construct($type, string $severity = 'INFO', $description = null)
    {
        $this->id = (Uuid::uuid1())->toString();
        $this->type = $type;
        $this->severity = $severity;
        $this->description = $description;

    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     *
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }


}