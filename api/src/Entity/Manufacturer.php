<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use Doctrine\Common\Collections\Collection;

/**
 * A manufacturer
 */
#[ApiResource(
    paginationItemsPerPage: 5,
    operations: [
        new Get(),
        new Post(),
        new GetCollection(),
        new Patch(),
        new Put(),
    ]
)]
#[ApiResource(
    uriTemplate: '/manufacturers/{id}/products',
    uriVariables: [
        'id' => new Link(
            fromClass: Product::class,
            fromProperty: 'manufacturer'
        )
    ],
    operations: [new Get()]
)]
#[ORM\Entity]
class Manufacturer
{
    /**
     * The id of the manufacturer
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['manufacturer.read'])]
    private ?int $id = null;

    /**
     * The name of the manufacturer
     */
    #[ORM\Column]
    #[
        Assert\NotBlank,
        Groups(['product.read'])
    ]
    private string $name = '';

    /**
     * The description of the manufacturer
     */
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $description = '';

    /**
     * The country code of the manufacturer
     */
    #[ORM\Column(length: 3)]
    #[Assert\NotBlank]
    private string $countryCode = '';

    /**
     * The date that the manufacturer was listed
     */
    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    private ?\DateTimeInterface $listedDate = null;

    /**
     * @var Product[] Available products from this manufacturer
     */
    #[ORM\OneToMany(
        targetEntity: Product::class,
        mappedBy: "manufacturer",
        cascade: ["persist", "remove"]
    )]
    #[ApiSubresource]
    private iterable $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getListedDate()
    {
        return $this->listedDate;
    }

    /**
     * @param \DateTimeInterface|null $listedDate
     */
    public function setListedDate($listedDate)
    {
        $this->listedDate = $listedDate;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }


}
