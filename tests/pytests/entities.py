from fastybird_devices_module.entities.connector import ConnectorEntity


class DummyConnectorEntity(ConnectorEntity):
    """
    Dummy connector entity

    @package        FastyBird:DevicesModule!
    @module         entities/connector

    @author         Adam Kadlec <adam.kadlec@fastybird.com>
    """

    __mapper_args__ = {"polymorphic_identity": "dummy"}

    # -----------------------------------------------------------------------------

    @property
    def type(self) -> str:
        """Connector type"""
        return "dummy"
