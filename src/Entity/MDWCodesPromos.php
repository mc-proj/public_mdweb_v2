<?php

namespace App\Entity;

use App\Repository\MDWCodesPromosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWCodesPromosRepository::class)
 */
class MDWCodesPromos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $type_promo;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $valeur;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $minimum_achat;

    /**
     * @ORM\Column(type="date")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $date_debut_validite;

    /**
     * @ORM\Column(type="date")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $date_fin_validite;

    /**
     * @ORM\OneToMany(targetEntity=MDWCodesPromosUsers::class, mappedBy="code_promo", orphanRemoval=true)
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=MDWFactures::class, mappedBy="code_promo")
     */
    private $Factures;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->Factures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTypePromo(): ?string
    {
        return $this->type_promo;
    }

    public function setTypePromo(string $type_promo): self
    {
        $this->type_promo = $type_promo;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(int $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }

    public function getMinimumAchat(): ?int
    {
        return $this->minimum_achat;
    }

    public function setMinimumAchat(int $minimum_achat): self
    {
        $this->minimum_achat = $minimum_achat;

        return $this;
    }

    public function getDateDebutValidite(): ?\DateTimeInterface
    {
        return $this->date_debut_validite;
    }

    public function setDateDebutValidite(\DateTimeInterface $date_debut_validite): self
    {
        $this->date_debut_validite = $date_debut_validite;

        return $this;
    }

    public function getDateFinValidite(): ?\DateTimeInterface
    {
        return $this->date_fin_validite;
    }

    public function setDateFinValidite(\DateTimeInterface $date_fin_validite): self
    {
        $this->date_fin_validite = $date_fin_validite;

        return $this;
    }

    /**
     * @return Collection|MDWCodesPromosUsers[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(MDWCodesPromosUsers $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setCodePromo($this);
        }

        return $this;
    }

    public function removeUser(MDWCodesPromosUsers $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCodePromo() === $this) {
                $user->setCodePromo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWFactures[]
     */
    public function getFactures(): Collection
    {
        return $this->Factures;
    }

    public function addFacture(MDWFactures $facture): self
    {
        if (!$this->Factures->contains($facture)) {
            $this->Factures[] = $facture;
            $facture->setCodePromo($this);
        }

        return $this;
    }

    public function removeFacture(MDWFactures $facture): self
    {
        if ($this->Factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getCodePromo() === $this) {
                $facture->setCodePromo(null);
            }
        }

        return $this;
    }
}
