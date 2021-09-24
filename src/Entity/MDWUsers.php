<?php

namespace App\Entity;

use App\Repository\MDWUsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MDWUsersRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà lié à un compte")
 */
class MDWUsers implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank(message = "Veuillez entrer une adresse email")
     * @Assert\Email(message = "Veuillez entrer une adresse email valide")
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank(message = "Vous devez accepter les conditions d'utilisation")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Veuillez entrer votre nom")
     * @Assert\Length(
     *         min = 3,
     *         max = 255,
     *         minMessage = "Votre Nom doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre Nom doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])] //ori alone
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Veuillez entrer votre prénom")
     * @Assert\Length(
     *         min = 3,
     *         max = 255,
     *         minMessage = "Votre Prénom doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre Prénom doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Veuillez entrer votre adresse")
     * @Assert\Length(
     *         min = 5,
     *         max = 255,
     *         minMessage = "Votre Adresse doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre Adresse doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $adresse;

    /**
     * @ORM\Column(type="string", length=45)
     * @Assert\NotBlank(message = "Veuillez entrer votre code postal")
     * @Assert\Length(
     *         min = 3,
     *         max = 45,
     *         minMessage = "Votre Code Postal doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre Code Postal doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $code_postal;

    /**
     * @ORM\Column(type="string", length=45)
     * @Assert\NotBlank(message = "Veuillez entrer votre ville")
     * @Assert\Length(
     *         min = 5,
     *         max = 45,
     *         minMessage = "Votre Ville doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre Ville doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $ville;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Assert\NotBlank(message = "Veuillez entrer votre numéro de téléphone")
     * @Assert\Length(
     *         min = 4,
     *         max = 45,
     *         minMessage = "Votre numéro de téléphone doit comporter au moins {{ limit }} caractères",
     *         maxMessage = "Votre numéro de téléphone doit comporter moins de {{ limit }} caractères"
     * )
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $telephone;

    /**
     * @ORM\OneToMany(targetEntity=MDWFactures::class, mappedBy="user")
     */
    private $factures;

    /**
     * @ORM\OneToMany(targetEntity=MDWAvis::class, mappedBy="user", orphanRemoval=true)
     */
    private $avis;

    /**
     * @ORM\OneToMany(targetEntity=MDWCodesPromosUsers::class, mappedBy="user", orphanRemoval=true)
     */
    private $codes_promos;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message = "Veuillez entrer votre pays")
     */
    #[Groups(['read:facture:MDWFacture'])]
    private $pays;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_modification;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $id_stripe;

    public function __construct()
    {
        $this->factures = new ArrayCollection();
        $this->avis = new ArrayCollection();
        $this->codes_promos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->code_postal;
    }

    public function setCodePostal(string $code_postal): self
    {
        $this->code_postal = $code_postal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection|MDWFactures[]
     */
    public function getFactures(): Collection
    {
        return $this->factures;
    }

    public function addFacture(MDWFactures $facture): self
    {
        if (!$this->factures->contains($facture)) {
            $this->factures[] = $facture;
            $facture->setUser($this);
        }

        return $this;
    }

    public function removeFacture(MDWFactures $facture): self
    {
        if ($this->factures->removeElement($facture)) {
            // set the owning side to null (unless already changed)
            if ($facture->getUser() === $this) {
                $facture->setUser(null);
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
            $avi->setUser($this);
        }

        return $this;
    }

    public function removeAvi(MDWAvis $avi): self
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getUser() === $this) {
                $avi->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MDWCodesPromosUsers[]
     */
    public function getCodesPromos(): Collection
    {
        return $this->codes_promos;
    }

    public function addCodesPromo(MDWCodesPromosUsers $codesPromo): self
    {
        if (!$this->codes_promos->contains($codesPromo)) {
            $this->codes_promos[] = $codesPromo;
            $codesPromo->setUser($this);
        }

        return $this;
    }

    public function removeCodesPromo(MDWCodesPromosUsers $codesPromo): self
    {
        if ($this->codes_promos->removeElement($codesPromo)) {
            // set the owning side to null (unless already changed)
            if ($codesPromo->getUser() === $this) {
                $codesPromo->setUser(null);
            }
        }

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): self
    {
        $this->pays = $pays;

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

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(\DateTimeInterface $date_modification): self
    {
        $this->date_modification = $date_modification;

        return $this;
    }

    public function getIdStripe(): ?string
    {
        return $this->id_stripe;
    }

    public function setIdStripe(?string $id_stripe): self
    {
        $this->id_stripe = $id_stripe;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function DateCreationAuto() {
        $this->date_creation = new \DateTime();
        $this->date_modification = $this->date_creation;
    }

    /**
     * @ORM\PreUpdate
     */
    public function MAJDateModification() {
        $this->date_modification = new \DateTime();
    }
}
