#!/usr/bin/env python3
"""Report each native graph separately; absent instrumentation is unknown."""
import argparse
import hashlib
import json
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument('raw_report', type=Path)
parser.add_argument('--inventory', type=Path, default=Path(__file__).resolve().parents[1] / 'SOURCE_INVENTORY.json')
args = parser.parse_args()
raw = json.loads(args.raw_report.read_text())
inventory = json.loads(args.inventory.read_text())
root = args.inventory.resolve().parents[1]
instrumented_root = raw.get('source_root', str(root))
if raw.get('baseline', inventory['production_baseline']) != inventory['production_baseline']:
    raise SystemExit('Raw report baseline differs from the source inventory.')
if hashlib.sha256((root / 'low_random/low_random.phar').read_bytes()).hexdigest() != inventory['archive_sha256']:
    raise SystemExit('Current archive differs from the pinned source inventory.')
if raw.get('archive_sha256', inventory['archive_sha256']) != inventory['archive_sha256']:
    raise SystemExit('Measured archive differs from the pinned source inventory.')
for path, metadata in inventory['sources'].items():
    if '!' not in path and hashlib.sha256((root / path).read_bytes()).hexdigest() != metadata['sha256']:
        raise SystemExit('Current source differs from the pinned source inventory: ' + path)
results = {}
for path, coverage in raw['coverage'].items():
    if path.startswith('phar://' + instrumented_root + '/low_random/'):
        name = path.removeprefix('phar://' + instrumented_root + '/').replace('.phar/', '.phar!/')
    elif path.startswith(instrumented_root + '/low_random/'):
        name = path.removeprefix(instrumented_root + '/')
        if name.endswith('.phar'):
            name += '!stub'
    else:
        continue
    lines = {int(line): hit for line, hit in coverage.get('lines', {}).items()}
    methods = []
    for method, graph in coverage.get('functions', {}).items():
        branches = graph.get('branches', {})
        nodes = list(branches.values()) if isinstance(branches, dict) else branches
        if not nodes:
            methods.append({'method': method, 'status': 'unknown-branch-graph'})
            continue
        first = min(node['line_start'] for node in nodes)
        last = max(max(node['line_start'], node['line_end']) for node in nodes)
        method_lines = {line: hit for line, hit in lines.items() if first <= line <= last and hit != -2}
        edges = []
        for node in nodes:
            outgoing = node.get('out', [])
            outgoing = outgoing.items() if isinstance(outgoing, dict) else enumerate(outgoing)
            hits = node.get('out_hit', [])
            for index, destination in outgoing:
                hit = hits.get(str(index), 0) if isinstance(hits, dict) else hits[int(index)]
                edges.append({'from_opcode': node['op_start'], 'to_opcode': destination, 'line': node['line_end'], 'hit': hit})
        methods.append({
            'method': method, 'first_line': first, 'last_line': last,
            'executable_lines': len(method_lines), 'covered_lines': sum(hit > 0 for hit in method_lines.values()),
            'uncovered_lines': [line for line, hit in method_lines.items() if hit < 0],
            'basic_blocks': len(nodes), 'covered_basic_blocks': sum(node['hit'] > 0 for node in nodes),
            'edges': len(edges), 'covered_edges': sum(edge['hit'] > 0 for edge in edges),
            'uncovered_edges': [edge for edge in edges if not edge['hit']],
            'status': 'at-target' if method_lines and all(hit > 0 for hit in method_lines.values()) and all(node['hit'] > 0 for node in nodes) and all(edge['hit'] > 0 for edge in edges) else 'incomplete',
        })
    results[name] = {'source_sha256': inventory['sources'].get(name, {}).get('sha256'), 'uncovered_file_lines': [line for line, hit in lines.items() if hit == -1], 'methods': methods}
for path, source in inventory['sources'].items():
    if path not in results:
        results[path] = {'source_sha256': source['sha256'], 'status': 'not-instrumented', 'methods': [{'method': method['name'], 'first_line': method['line'], 'status': 'not-instrumented'} for method in source['functions']]}
print(json.dumps({'raw_report': str(args.raw_report), 'php': raw['php'], 'xdebug': raw['xdebug'], 'note': 'One runtime/process/compiled graph only. Unknown or absent branches are never 100%. Native child reports remain separate.', 'files': results}, indent=2))
