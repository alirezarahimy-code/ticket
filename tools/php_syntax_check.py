#!/usr/bin/env python3
"""ابزارِ کمکیِ محلی: بررسی نحویِ فایل‌های PHP با tree-sitter (برای نبود PHP در این جعبه‌شن).
کاربرد: python3 tools/php_syntax_check.py file1.php [file2.php ...]"""
import sys
from tree_sitter import Language, Parser
import tree_sitter_php as tsphp

LANG = Language(tsphp.language_php())
parser = Parser(LANG)

failed = False
for path in sys.argv[1:]:
    data = open(path, 'rb').read()
    tree = parser.parse(data)
    errors = []
    def walk(node):
        if node.type == 'ERROR' or node.is_missing:
            errors.append((node.start_point, node.end_point, data[node.start_byte:node.start_byte+60].decode('utf-8', 'replace').replace('\n', '\\n')))
        for child in node.children:
            walk(child)
    walk(tree.root_node)
    if errors:
        failed = True
        print(f'✘ {path}')
        for start, end, snippet in errors[:8]:
            print(f'    line {start[0]+1}:{start[1]+1} .. {end[0]+1}:{end[1]+1} :: {snippet}')
    else:
        print(f'✓ {path}')
sys.exit(1 if failed else 0)
