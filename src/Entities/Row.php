<?php declare(strict_types = 1);

/**
 * Row.php
 *
 * @license        More in LICENSE.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities;

use Consistence\Doctrine\Enum\EnumAnnotation as Enum;
use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule\Exceptions;
use FastyBird\ModulesMetadata\Types as ModulesMetadataTypes;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use IPub\DoctrineTimestampable;
use Nette\Utils;
use Ramsey\Uuid;
use Throwable;

abstract class Row implements IRow
{

	use TKey;
	use TEntity;
	use TEntityParams;
	use DoctrineTimestampable\Entities\TEntityCreated;
	use DoctrineTimestampable\Entities\TEntityUpdated;

	/**
	 * @var Uuid\UuidInterface
	 *
	 * @ORM\Id
	 * @ORM\Column(type="uuid_binary", name="configuration_id")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	protected Uuid\UuidInterface $id;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", name="configuration_key", length=50)
	 */
	protected ?string $key = null;

	/**
	 * @var string
	 *
	 * @IPubDoctrine\Crud(is="required")
	 * @ORM\Column(type="string", name="configuration_identifier", length=50, nullable=false)
	 */
	protected string $identifier;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_name", nullable=true, options={"default": null})
	 */
	protected ?string $name = null;

	/**
	 * @var string|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="text", name="configuration_comment", nullable=true, options={"default": null})
	 */
	protected ?string $comment = null;

	/**
	 * @var ModulesMetadataTypes\DataTypeType
	 *
	 * @Enum(class=ModulesMetadataTypes\DataTypeType::class)
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 * @ORM\Column(type="string_enum", name="configuration_data_type", nullable=false)
	 */
	protected $dataType;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_default", nullable=true, options={"default": null})
	 */
	protected $default = null;

	/**
	 * @var mixed|null
	 *
	 * @IPubDoctrine\Crud(is="writable")
	 * @ORM\Column(type="string", name="configuration_value", nullable=true, options={"default": null})
	 */
	protected $value = null;

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?float $min = null;

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?float $max = null;

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected ?float $step = null;

	/**
	 * @var mixed[]
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected array $values = [];

	/**
	 * @param string $identifier
	 * @param Uuid\UuidInterface|null $id
	 *
	 * @throws Throwable
	 */
	public function __construct(
		string $identifier,
		?Uuid\UuidInterface $id = null
	) {
		$this->id = $id ?? Uuid\Uuid::uuid4();

		$this->identifier = $identifier;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getComment(): ?string
	{
		return $this->comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setComment(?string $comment): void
	{
		$this->comment = $comment;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataType(): ModulesMetadataTypes\DataTypeType
	{
		return $this->dataType;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDataType(string $dataType): void
	{
		if (!ModulesMetadataTypes\DataTypeType::isValidValue($dataType)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided data type "%s" is not valid', $dataType));
		}

		$this->dataType = ModulesMetadataTypes\DataTypeType::get($dataType);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefault(?string $default): void
	{
		$this->default = $default;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		if ($this->value === null) {
			return null;
		}

		if ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)) {
			return (float) $this->value;

		} elseif ($this->dataType->isInteger()) {
			return (int) $this->value;

		} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_BOOLEAN)) {
			return $this->value === '1' || Utils\Strings::lower((string) $this->value) === 'true';
		}

		return (string) $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue(?string $value): void
	{
		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMin(): ?float
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('min_value');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMin(?float $min): void
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		if ($this->getMin() !== $min) {
			$this->setParam('min_value', $min);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasMin(): bool
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('min_value') !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMax(): ?float
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('max_value');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMax(?float $max): void
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		if ($this->getMax() !== $max) {
			$this->setParam('max_value', $max);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasMax(): bool
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('max_value') !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStep(): ?float
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('step_value');
	}

	/**
	 * {@inheritDoc}
	 */
	public function setStep(?float $step): void
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		if ($this->getStep() !== $step) {
			$this->setParam('step_value', $step);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasStep(): bool
	{
		if (
			!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT)
			&& !$this->dataType->isInteger()
		) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('step_value') !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValues(): array
	{
		if (!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		return $this->getParam('select_values', []);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValues(array $values): void
	{
		if (!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		$this->setParam('select_values', []);

		foreach ($values as $value) {
			$this->addValue($value);
		}
	}

	/**
	 * @param Utils\ArrayHash $value
	 *
	 * @return void
	 */
	private function addValue(Utils\ArrayHash $value): void
	{
		if (!$this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			throw new Exceptions\InvalidStateException(sprintf('This method is not allowed for %s data type', $this->dataType->getValue()));
		}

		$values = $this->getParam('select_values', []);

		if ($value->offsetExists('value') && $value->offsetExists('name')) {
			$values[] = [
				'name'  => (string) $value->offsetGet('name'),
				'value' => (string) $value->offsetGet('value'),
			];
		}

		$this->setParam('select_values', $values);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		$data = [
			'id'         => $this->getPlainId(),
			'key'        => $this->getKey(),
			'identifier' => $this->getIdentifier(),
			'name'       => $this->getName(),
			'comment'    => $this->getComment(),
			'default'    => $this->getDefault(),
			'value'      => $this->getValue(),
		];

		if ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_FLOAT) || $this->dataType->isInteger()) {
			return array_merge($data, [
				'min'  => $this->getMin(),
				'max'  => $this->getMax(),
				'step' => $this->getStep(),
			]);

		} elseif ($this->dataType->equalsValue(ModulesMetadataTypes\DataTypeType::DATA_TYPE_ENUM)) {
			return array_merge($data, [
				'values' => $this->getValues(),
			]);
		}

		return $data;
	}

}
