<?php

namespace App\Entity;

use App\Repository\MDWFacturesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWFacturesRepository::class)
 */
class MDWFactures
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $date_creation;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $montant_total;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $montant_ht;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $montant_ttc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity=MDWUsers::class, inversedBy="factures")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=MDWFacturesProduits::class, mappedBy="facture", orphanRemoval=true)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $produit;

    /**
     * @ORM\ManyToOne(targetEntity=MDWAdressesLivraison::class, inversedBy="Factures")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $adresseLivraison;

    /**
     * @ORM\ManyToOne(targetEntity=MDWCodesPromos::class, inversedBy="Factures")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $code_promo;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getMontantTotal(): ?int
    {
        return $this->montant_total;
    }

    public function setMontantTotal(int $montant_total): self
    {
        $this->montant_total = $montant_total;

        return $this;
    }

    public function getMontantHt(): ?int
    {
        return $this->montant_ht;
    }

    public function setMontantHt(int $montant_ht): self
    {
        $this->montant_ht = $montant_ht;

        return $this;
    }

    public function getMontantTtc(): ?int
    {
        return $this->montant_ttc;
    }

    public function setMontantTtc(int $montant_ttc): self
    {
        $this->montant_ttc = $montant_ttc;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    /**
     * @return Collection|MDWFacturesProduits[]
     */
    public function getProduit(): Collection
    {
        return $this->produit;
    }

    public function addProduit(MDWFacturesProduits $produit): self
    {
        if (!$this->produit->contains($produit)) {
            $this->produit[] = $produit;
            $produit->setFacture($this);
        }

        return $this;
    }

    public function removeProduit(MDWFacturesProduits $produit): self
    {
        if ($this->produit->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getFacture() === $this) {
                $produit->setFacture(null);
            }
        }

        return $this;
    }

    public function getAdresseLivraison(): ?MDWAdressesLivraison
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?MDWAdressesLivraison $adresseLivraison): self
    {
        $this->adresseLivraison = $adresseLivraison;

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
