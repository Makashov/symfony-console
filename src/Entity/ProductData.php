<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="strProductCode", columns={"strProductCode"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProductDataRepository")
 */
class ProductData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $intProductDataId;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $strProductName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $strProductDesc;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $strProductCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dtmAdded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dtmDiscontinued;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $cost;

    public function getCost()
    {
        return $this->cost;
    }

    public function setCost($cost): void
    {
        $this->cost = (float)$cost;
    }

    public function getStock()
    {
        return $this->stock;
    }

    public function setStock($stock): void
    {
        $this->stock = (int)$stock;
    }

    /**
     * @ORM\Column(type="datetime", nullable=false, options={"default"="current_timestamp()"})
     */
    private $stmTimestamp;

    public function createFromArray(array $data)
    {
        foreach ($data as $property => $value) {
            $method = sprintf('set%s', ucwords($property));
            $this->$method($value);
        }

        return $this;
    }

    public function getIntProductDataId(): ?int
    {
        return $this->intProductDataId;
    }

    public function getStrProductName(): ?string
    {
        return $this->strProductName;
    }

    public function setStrProductName(string $strProductName): self
    {
        $this->strProductName = $strProductName;

        return $this;
    }

    public function getStrProductDesc(): ?string
    {
        return $this->strProductDesc;
    }

    public function setStrProductDesc(string $strProductDesc): self
    {
        $this->strProductDesc = $strProductDesc;

        return $this;
    }

    public function getStrProductCode(): ?string
    {
        return $this->strProductCode;
    }

    public function setStrProductCode(string $strProductCode): self
    {
        $this->strProductCode = $strProductCode;

        return $this;
    }

    public function getDtmAdded(): ?\DateTimeInterface
    {
        return $this->dtmAdded;
    }

    public function setDtmAdded(\DateTimeInterface $dtmAdded): self
    {
        $this->dtmAdded = $dtmAdded;

        return $this;
    }

    public function getDtmDiscontinued(): ?\DateTimeInterface
    {
        return $this->dtmDiscontinued;
    }

    public function setDtmDiscontinued($dtmDiscontinued = null): self
    {
        $this->dtmDiscontinued = $dtmDiscontinued == 'yes' ? new \DateTime() : null;

        return $this;
    }

    public function getStmTimestamp(): ?\DateTimeInterface
    {
        return $this->stmTimestamp;
    }

    public function setStmTimestamp(\DateTimeInterface $stmTimestamp): self
    {
        $this->stmTimestamp = $stmTimestamp;

        return $this;
    }
}
