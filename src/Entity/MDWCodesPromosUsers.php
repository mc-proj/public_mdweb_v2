<?php

namespace App\Entity;

use App\Repository\MDWCodesPromosUsersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWCodesPromosUsersRepository::class)
 */
class MDWCodesPromosUsers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date_utilisation;

    /**
     * @ORM\ManyToOne(targetEntity=MDWUsers::class, inversedBy="codes_promos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=MDWCodesPromos::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $code_promo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateUtilisation(): ?\DateTimeInterface
    {
        return $this->date_utilisation;
    }

    public function setDateUtilisation(\DateTimeInterface $date_utilisation): self
    {
        $this->date_utilisation = $date_utilisation;

        return $this;
    }

    public function getUser(): ?MDWUsers
    {
        return $this->user;
    }

    public function setUser(?MDWUsers $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCodePromo(): ?MDWCodesPromos
    {
        return $this->code_promo;
    }

    public function setCodePromo(?MDWCodesPromos $code_promo): self
    {
        $this->code_promo = $code_promo;

        return $this;
    }
}
