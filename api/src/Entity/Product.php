<?php

namespace App\Entity;


use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

/**
 * A product
 */
#[
    ApiResource(
        normalizationContext: ['groups' => ['product.read']],
        denormalizationContext: ['groups' => ['product.write']],
        paginationItemsPerPage: 5,
        operations: [
            new Get(security: "is_granted('ROLE_USER')"),
            new Post(security: "is_granted('ROLE_ADMIN')"),
            new GetCollection(security: "is_granted('ROLE_USER')"),
            new Patch(security: "is_granted('ROLE_ADMIN')"),
            new Put(security: "is_granted('ROLE_USER') and object.getOwner() == user.id", securityMessage: "Продукт может быть обновлен только владельцем"),
            new Delete(security: "is_granted('ROLE_ADMIN')"),
        ]
    ),
    ApiFilter(
        SearchFilter::class,
        properties: [
            'name' => SearchFilter::STRATEGY_PARTIAL,
            'description' => SearchFilter::STRATEGY_PARTIAL,
            'manufacturer.countryCode' => SearchFilter::STRATEGY_EXACT,
            'manufacturer.id' => SearchFilter::STRATEGY_EXACT,
        ]
    ),
    ApiFilter(
        OrderFilter::class,
        properties: ['issueDate']
    )
]
#[ApiResource(
    uriTemplate: '/products/{id}/manufacturers',
    uriVariables: [
        'id' => new Link(
            fromClass: Manufacturer::class,
            fromProperty: 'products'
        )
    ],
    operations: [new Get()]
)]
#[ORM\Entity]
class Product
{
    /**
     * The id of the product.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['product.read'])]
    private ?int $id = null;

    /**
     * The MPN (manufacturer part number) of the product
     */
    #[ORM\Column]
    #[
        Assert\NotNull,
        Groups(['product.read', 'product.write'])
    ]
    private ?string $mpn = null;

    /**
     * The name of the product.
     */
    #[ORM\Column]
    #[
        Assert\NotBlank,
        Groups(['product.read', 'product.write'])
    ]
    private string $name = '';

    /**
     * The description of the product.
     */
    #[ORM\Column(type: 'text')]
    #[
        Assert\NotBlank,
        Groups(['product.read', 'product.write'])
    ]
    private string $description = '';

    /**
     * The date of issue of the product
     */
    #[ORM\Column(type: 'datetime')]
    #[
        Assert\NotNull,
        Groups(['product.read', 'product.write'])
    ]
    private ?\DateTimeInterface $issueDate = null;

    /**
     * The manufacturer of the product
     */
    #[ORM\ManyToOne(targetEntity: Manufacturer::class, inversedBy: "products")]
    #[
        Groups(['product.read', 'product.write']),
        Assert\NotNull
    ]
    private ?Manufacturer $manufacturer = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[
        Groups(['product.read', 'product.write']),
    ]
    private ?User $owner = null;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getMpn()
    {
        return $this->mpn;
    }

    /**
     * @param string|null $mpn
     */
    public function setMpn($mpn)
    {
        $this->mpn = $mpn;
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
     * @return \DateTimeInterface|null
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * @param \DateTimeInterface|null $issueDate
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;
    }

    /**
     * @return Manufacturer|null
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param Manufacturer|null $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
