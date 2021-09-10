<?php

namespace App\Entity;

use App\Repository\MDWPaniersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MDWPaniersRepository::class)
 */
class MDWPaniers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $commande_terminee;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant_ht;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant_ttc;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $message;

    /**
     * @ORM\OneToOne(targetEntity=MDWAdressesLivraison::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $adresse_livraison;

    /**
     * @ORM\OneToMany(targetEntity=MDWPaniersProduits::class, mappedBy="panier", orphanRemoval=true)
     */
    private $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeTerminee(): ?bool
    {
        return $this->commande_terminee;
    }

    public function setCommandeTerminee(bool $commande_terminee): self
    {
        $this->commande_terminee = $commande_terminee;

        return $this;
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

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getAdresseLivraison(): ?MDWAdressesLivraison
    {
        return $this->adresse_livraison;
    }

    public function setAdresseLivraison(MDWAdressesLivraison $adresse_livraison): self
    {
        $this->adresse_livraison = $adresse_livraison;

        return $this;
    }

    /**
     * @return Collection|MDWPaniersProduits[]
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(MDWPaniersProduits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->setPanier($this);
        }

        return $this;
    }

    public function removeProduit(MDWPaniersProduits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getPanier() === $this) {
                $produit->setPanier(null);
            }
        }

        return $this;
    }
}
