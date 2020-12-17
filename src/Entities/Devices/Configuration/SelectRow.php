<?php declare(strict_types = 1);

/**
 * SelectRow.php
 *
 * @license        More in license.md
 * @copyright      https://www.fastybird.com
 * @author         Adam Kadlec <adam.kadlec@fastybird.com>
 * @package        FastyBird:DevicesModule!
 * @subpackage     Entities
 * @since          0.1.0
 *
 * @date           01.11.18
 */

namespace FastyBird\DevicesModule\Entities\Devices\Configuration;

use Doctrine\ORM\Mapping as ORM;
use FastyBird\DevicesModule;
use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;
use Nette\Utils;

/**
 * @ORM\Entity
 */
class SelectRow extends Row implements ISelectRow
{

	/**
	 * @var mixed[]
	 * @IPubDoctrine\Crud(is="writable")
	 */
	protected array $values = [];

	/** @var string */
	protected string $type = DevicesModule\Constants::DATA_TYPE_SELECT;

	/**
	 * {@inheritDoc}
	 */
	public function getValue(): ?string
	{
		if ($this->value === null) {
			return null;
		}

		return (string) $this->value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'values' => $this->getValues(),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValues(): array
	{
		return $this->getParam('select_values', []);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValues(array $values): void
	{
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
		$values = $this->getParam('select_values', []);

		if ($value->offsetExists('value') && $value->offsetExists('name')) {
			$values[] = [
				'name'  => (string) $value->offsetGet('name'),
				'value' => (string) $value->offsetGet('value'),
			];
		}

		$this->setParam('select_values', $values);
	}

}
