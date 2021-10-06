<?php

namespace App\Entity;

use App\Repository\MDWProduitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use DateTime;

//#[Groups(['read:facture:MDWFacture'])]

/**
 * @ORM\Entity(repositoryClass=MDWProduitsRepository::class)
 */
class MDWProduits
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=45)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $reference;

    /**
     * @ORM\Column(type="string", length=45)
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $nom;

    /**
     * @ORM\Column(type="boolean")
     */
    private $est_visible;

    /**
     * @ORM\Column(type="boolean")
     */
    private $mis_en_avant;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $description_courte;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $description;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $date_debut_promo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $date_fin_promo;

    /**
     * @ORM\Column(type="boolean")
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $tva_active;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite_stock;

    /**
     * @ORM\Column(type="integer")
     */
    private $limite_basse_stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $commandable_sans_stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $est_evaluable;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $tarif;

    /**
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $tarif_promo;

    /**
     * @ORM\Column(type="date")
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $date_creation;

    /**
     * @ORM\ManyToOne(targetEntity=MDWTauxTva::class, inversedBy="produits")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $taux_tva;

    /**
     * @ORM\OneToMany(targetEntity=MDWImages::class, mappedBy="produit", orphanRemoval=true)
     */
    #[Groups(['read:carte:MDWProduit', 'read:facture:MDWFacture'])]
    private $images;

    /**
     * @ORM\ManyToMany(targetEntity=MDWCategories::class, inversedBy="produits")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity=MDWProduits::class)
     */
    private $produits_suggeres;

    /**
     * @ORM\OneToMany(targetEntity=MDWCaracteristiques::class, mappedBy="produit", orphanRemoval=true)
     */
    private $caracteristiques;

    /**
     * @ORM\OneToMany(targetEntity=MDWAvis::class, mappedBy="produit", orphanRemoval=true)
     */
    private $avis;

    /**
     * @ORM\OneToMany(targetEntity=MDWFacturesProduits::class, mappedBy="produit")
     */
    private $factures;

    /**
     * @ORM\OneToMany(targetEntity=MDWPaniersProduits::class, mappedBy="produit", orphanRemoval=true)
     */
    private $paniers;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->produits_suggeres = new ArrayCollection();
        $this->caracteristiques = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->paniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEstVisible(): ?bool
    {
        return $this->est_visible;
    }

    public function setEstVisible(bool $est_visible): self
    {
        $this->est_visible = $est_visible;

        return $this;
    }

    public function getMisEnAvant(): ?bool
    {
        return $this->mis_en_avant;
    }

    public function setMisEnAvant(bool $mis_en_avant): self
    {
        $this->mis_en_avant = $mis_en_avant;

        return $this;
    }

    public function getDescriptionCourte(): ?string
    {
        return $this->description_courte;
    }

    public function setDescriptionCourte(string $description_courte): self
    {
        $this->description_courte = $description_courte;

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

    public function getDateDebutPromo(): ?\DateTimeInterface
    {
        return $this->date_debut_promo;
    }

    public function setDateDebutPromo(?\DateTimeInterface $date_debut_promo): self
    {
        $this->date_debut_promo = $date_debut_promo;

        return $this;
    }

    public function getDateFinPromo(): ?\DateTimeInterface
    {
        return $this->date_fin_promo;
    }

    public function setDateFinPromo(?\DateTimeInterface $date_fin_promo): self
    {
        $this->date_fin_promo = $date_fin_promo;

        return $this;
    }

    public function getTvaActive(): ?bool
    {
        return $this->tva_active;
    }

    public function setTvaActive(bool $tva_active): self
    {
        $this->tva_active = $tva_active;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantite_stock;
    }

    public function setQuantiteStock(int $quantite_stock): self
    {
        $this->quantite_stock = $quantite_stock;

        return $this;
    }

    public function getLimiteBasseStock(): ?int
    {
        return $this->limite_basse_stock;
    }

    public function setLimiteBasseStock(int $limite_basse_stock): self
    {
        $this->limite_basse_stock = $limite_basse_stock;

        return $this;
    }

    public function getCommandableSansStock(): ?bool
    {
        return $this->commandable_sans_stock;
    }

    public function setCommandableSansStock(bool $commandable_sans_stock): self
    {
        $this->commandable_sans_stock = $commandable_sans_stock;

        return $this;
    }

    public function getEstEvaluable(): ?bool
    {
        return $this->est_evaluable;
    }

    public function setEstEvaluable(bool $est_evaluable): self
    {
        $this->est_evaluable = $est_evaluable;

        return $this;
    }

    public function getTarif(): ?int
    {
        return $this->tarif;
    }

    public function setTarif(int $tarif): self
    {
        $this->tarif = $tarif;

        return $this;
    }

    public function getTarifPromo(): ?int
    {
        return $this->tarif_promo;
    }

    public function setTarifPromo(int $tarif_promo): self
    {
        $this->tarif_promo = $tarif_promo;

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

    public function getTauxTva(): ?MDWTauxTva
    {
        return $this->taux_tva;
    }

    public function setTauxTva(?MDWTauxTva $taux_tva): self
    {
        $this->taux_tva = $taux_tva;

        return $this;
    }

    /**
     * @return Collection|MDWImages[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(MDWImages $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduit($this);
        }

        return $this;
    }

    public function removeImage(MDWImages $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduit() === $this) {
                $image->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWCategories[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(MDWCategories $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(MDWCategories $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getProduitsSuggeres(): Collection
    {
        return $this->produits_suggeres;
    }

    public function addProduitsSuggere(self $produitsSuggere): self
    {
        if (!$this->produits_suggeres->contains($produitsSuggere)) {
            $this->produits_suggeres[] = $produitsSuggere;
        }

        return $this;
    }

    public function removeProduitsSuggere(self $produitsSuggere): self
    {
        $this->produits_suggeres->removeElement($produitsSuggere);

        return $this;
    }

    /**
     * @return Collection|MDWCaracteristiques[]
     */
    public function getCaracteristiques(): Collection
    {
        return $this->caracteristiques;
    }

    public function addCaracteristique(MDWCaracteristiques $caracteristique): self
    {
        if (!$this->caracteristiques->contains($caracteristique)) {
            $this->caracteristiques[] = $caracteristique;
            $caracteristique->setProduit($this);
        }

        return $this;
    }

    public function removeCaracteristique(MDWCaracteristiques $caracteristique): self
    {
        if ($this->caracteristiques->removeElement($caracteristique)) {
            // set the owning side to null (unless already changed)
            if ($caracteristique->getProduit() === $this) {
                $caracteristique->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWAvis[]
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(MDWAvis $avi): self
    {
        if (!$this->avis->contains($avi)) {
            $this->avis[] = $avi;
            $avi->setProduit($this);
        }

        return $this;
    }

    public function removeAvi(MDWAvis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getProduit() === $this) {
                $avi->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWFacturesProduits[]
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(MDWFacturesProduits $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setProduit($this);
        }

        return $this;
    }

    public function removeFacture(MDWFacturesProduits $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getProduit() === $this) {
                $facture->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWPaniersProduits[]
     */
    public function getPaniers(): Collection
    {
        return $this->paniers;
    }

    public function addPanier(MDWPaniersProduits $panier): self
    {
        if (!$this->paniers->contains($panier)) {
            $this->paniers[] = $panier;
            $panier->setProduit($this);
        }

        return $this;
    }

    public function removePanier(MDWPaniersProduits $panier): self
    {
        if ($this->paniers->removeElement($panier)) {
            // set the owning side to null (unless already changed)
            if ($panier->getProduit() === $this) {
                $panier->setProduit(null);
            }
        }

        return $this;
    }

    //retourne tarif ttc d'un produit avec promo si besoin (! controle promo par rapport à la date de l'appel de la methode)
    /*public function getTarifEffectif() {
        $tarif_ht = $this->getTarif();
        $tarif_ttc = 0;
        $now = new DateTime();

        if($this->getDateDebutPromo() <= $now || $this->getDateFinPromo() >= $now) {
            $tarif_ht = $this->getTarifPromo();
        }

        if($this->getTvaActive()) {
            $tarif_ttc = $tarif_ht + ($tarif_ht * $this->getTauxTva()->getTaux()/10000);
        }

        return [
            'ht' => $tarif_ht,
            'ttc' => $tarif_ttc
        ];
    }*/

    //date en parametre doit etre un DateTime
    public function getTarifEffectif($date_achat=null) {
        $tarif_ht = $this->getTarif();
        $tarif_ttc = 0;

        if($date_achat === null) {
            $date_achat = new DateTime();
        }

        if($this->getDateDebutPromo() <= $date_achat || $this->getDateFinPromo() >= $date_achat) {
            $tarif_ht = $this->getTarifPromo();
        }

        if($this->getTvaActive()) {
            $tarif_ttc = $tarif_ht + ($tarif_ht * $this->getTauxTva()->getTaux()/10000);
        }

        return [
            'ht' => $tarif_ht,
            'ttc' => $tarif_ttc
        ];
    }
    //
}
