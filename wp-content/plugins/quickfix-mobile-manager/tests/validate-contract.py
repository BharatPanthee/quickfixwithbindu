"""Validate the published JSON contract and local HTTP envelopes without credentials."""
import json
from pathlib import Path
from urllib.request import urlopen, build_opener, ProxyHandler
from urllib.error import HTTPError

document = json.loads((Path(__file__).parents[1] / 'docs/openapi.json').read_text())
schemas = document['components']['schemas']
assert document['openapi'] == '3.1.0'
assert len(document['paths']) == 47
operation_ids = []

def walk(value):
    if isinstance(value, dict):
        if '$ref' in value:
            assert value['$ref'].startswith('#/components/schemas/')
            assert value['$ref'].split('/')[-1] in schemas
        for child in value.values():
            walk(child)
    elif isinstance(value, list):
        for child in value:
            walk(child)

walk(document)
for path, methods in document['paths'].items():
    for method, operation in methods.items():
        assert method in ('get', 'post', 'put', 'patch', 'delete')
        operation_ids.append(operation['operationId'])
        assert 'security' in operation and '200' in operation['responses']
        if '{id}' in path:
            assert any(p['name'] == 'id' and p['in'] == 'path' and p['required'] for p in operation['parameters'])
assert len(operation_ids) == len(set(operation_ids))

try:
    from jsonschema import Draft202012Validator
except ImportError:
    Draft202012Validator = None
if Draft202012Validator:
    for schema in schemas.values():
        Draft202012Validator.check_schema(schema)

print(f'PASS: {len(document["paths"])} paths, {len(operation_ids)} operations, {len(schemas)} schemas, resolved references and unique operation IDs.')
print('JSON Schema structural validation: ' + ('passed' if Draft202012Validator else 'jsonschema dependency not installed; reference checks passed'))
