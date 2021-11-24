from typing import Dict, Union, List, Any


class DBSessionContextManager(object):
    def __call__(self, *args: Any, **kwargs: Any) -> Any: ...

class EntityMeta(type): ...

class Entity:
    def to_dict(self, only: Union[List[str], str, None] = None, exclude: Union[List[str], str, None] = None, with_collections: bool = False, with_lazy: bool = False, related_objects: bool = False) -> Dict: ...

db_session = DBSessionContextManager()
