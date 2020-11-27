<?php declare(strict_types = 1);

/**
 * NumberRow.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           26.10.18
 */

namespace FastyBird\DevicesModule\Entities\Channels\Configuration;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 */
class NumberRow extends Row implements INumberRow
{

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected $min = null;

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected $max = null;

	/**
	 * @var float|null
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected $step = null;

	/** @var string */
	protected $type = DevicesModule\Constants::DATA_TYPE_NUMBER;

	/**
	 * {@inheritDoc}
	 */
	public function setMin(?float $min): void
	{
		if ($this->getMin() !== $min) {
			$this->setParam('min_value', $min);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMin(): ?float
	{
		return $this->getParam('min_value', null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasMin(): bool
	{
		return $this->getParam('min_value', null) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setMax(?float $max): void
	{
		if ($this->getMax() !== $max) {
			$this->setParam('max_value', $max);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMax(): ?float
	{
		return $this->getParam('max_value', null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasMax(): bool
	{
		return $this->getParam('max_value', null) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setStep(?float $step): void
	{
		if ($this->getStep() !== $step) {
			$this->setParam('step_value', $step);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getStep(): ?float
	{
		return $this->getParam('step_value', null);
	}

	/**
	 * {@inheritDoc}
	 */
	public function hasStep(): bool
	{
		return $this->getParam('step_value', null) !== null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue(): ?float
	{
		if ($this->value === null) {
			return null;
		}

		return (float) $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'min'  => $this->getMin(),
			'max'  => $this->getMax(),
			'step' => $this->getStep(),
		]);
	}

}
