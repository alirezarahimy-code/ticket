"""Validate the immutable 1.62.59 historical index without opening archives."""
import re
import subprocess
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BASE = '860185dc2f389adc4a58e13e6bc4837fe96eaf32'
DOC = ROOT / 'docs/CHANGE-HISTORY-THROUGH-1.62.59-FA.md'


def git(*args):
    return subprocess.check_output(['git', *args], cwd=ROOT, text=True)


def expected_paths():
    docs, packages = set(), set()
    for path in git('ls-tree', '-r', '--name-only', BASE).splitlines():
        lower = path.lower()
        suffix = Path(path).suffix.lower()
        if lower.startswith('docs/') and suffix in {'.md', '.txt', '.tsv', '.json', '.html'}:
            docs.add(path)
        elif lower.startswith('updates/') and (Path(path).name.lower() == 'manifest.json' or suffix in {'.md', '.txt'}):
            docs.add(path)
        elif lower.startswith('downloads/') and suffix in {'.md', '.txt', '.html', '.json'}:
            docs.add(path)
        elif '/' not in path and suffix in {'.md', '.txt'}:
            docs.add(path)
        if lower.startswith('downloads/') and lower.endswith('.zip'):
            packages.add(path)
    return docs, packages


class HistoricalIndexTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.text = DOC.read_text(encoding='utf-8')

    def test_version_boundary_and_future_session_pointer(self):
        self.assertEqual(git('show', BASE + ':VERSION').strip(), '1.62.59')
        self.assertIn(BASE, self.text)
        self.assertIn('docs/CHANGE-HISTORY-THROUGH-1.62.59-FA.md', (ROOT / 'AGENTS.md').read_text(encoding='utf-8'))
        self.assertIn('حافظهٔ خودکار', self.text)

    def test_complete_tree_inventory_without_opening_zip(self):
        docs_section = self.text.split('## ۴.', 1)[1].split('## ۵.', 1)[0]
        packages_section = self.text.split('## ۵.', 1)[1]
        listed_docs = re.findall(r'^- `([^`]+)`$', docs_section, re.M)
        listed_packages = re.findall(r'^- `([^`]+)`$', packages_section, re.M)
        expected_docs, expected_packages = expected_paths()
        self.assertEqual(len(listed_docs), len(set(listed_docs)))
        self.assertEqual(len(listed_packages), len(set(listed_packages)))
        self.assertEqual(set(listed_docs), expected_docs)
        self.assertEqual(set(listed_packages), expected_packages)

    def test_all_reachable_commits_if_full_history_available(self):
        section = self.text.split('## ۳.', 1)[1].split('## ۴.', 1)[0]
        commits = re.findall(r'^### `([0-9a-f]{40})`$', section, re.M)
        self.assertEqual(len(commits), 329)
        self.assertEqual(len(set(commits)), 329)
        self.assertIn(BASE, commits)
        if git('rev-parse', '--is-shallow-repository').strip() == 'true':
            self.skipTest('full Git history is unavailable in this shallow checkout')
        self.assertEqual(set(commits), set(git('rev-list', BASE).splitlines()))
        raw = git('log', '--reverse', '--topo-order', '-z', '--format=%H%x00%B', BASE).split('\0')
        original = {raw[i]: raw[i + 1].rstrip('\n') for i in range(0, len(raw) - 1, 2)}
        blocks = re.split(r'^### `([0-9a-f]{40})`\n', section, flags=re.M)
        for i in range(1, len(blocks), 2):
            sha, block = blocks[i:i + 2]
            self.assertIn('https://github.com/alirezarahimy-code/ticket/commit/' + sha, block)
            message = '\n'.join(line[2:] for line in block.splitlines() if line.startswith('> '))
            self.assertEqual(message, original[sha], sha)

    def test_pull_requests_and_comment_scope(self):
        section = self.text.split('## ۲.', 1)[1].split('## ۳.', 1)[0]
        numbers = re.findall(r'^### درخواست #(\d+):', section, re.M)
        self.assertEqual(numbers, ['1', '2', '3', '4', '5', '6'])
        self.assertIn('صفر مورد', section)
        self.assertIn('هرگز در Git/GitHub ذخیره نشده‌اند', self.text)


if __name__ == '__main__':
    unittest.main()
