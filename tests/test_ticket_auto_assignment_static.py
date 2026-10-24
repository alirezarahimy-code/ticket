from pathlib import Path
import unittest

ROOT = Path(__file__).resolve().parents[1]


class TicketAutoAssignmentStaticTests(unittest.TestCase):
    def test_regular_managers_are_limited_to_the_ticket_department(self) -> None:
        source = (ROOT / "index.php").read_text(encoding="utf-8")
        start = source.index("function choose_handling_agent(")
        end = source.index("\nfunction ", start + 10)
        chooser = source[start:end]
        self.assertIn("function choose_handling_agent(string $serviceGroup, ?int $departmentId = null)", chooser)
        self.assertIn('u.role <> "manager" OR (? > 0 AND u.department_id = ?)', chooser)
        self.assertIn("$fallback->execute([$serviceGroup, $departmentId, $departmentId]);", chooser)

    def test_ticket_creation_passes_its_resolved_department_to_auto_assignment(self) -> None:
        source = (ROOT / "index.php").read_text(encoding="utf-8")
        self.assertIn("$agentId = choose_handling_agent($serviceGroup, $departmentId);", source)


if __name__ == "__main__":
    unittest.main()
