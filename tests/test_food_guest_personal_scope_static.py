from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def test_personal_and_privileged_guest_routes_are_isolated():
    text = (ROOT / 'food-order.php').read_text(encoding='utf-8')
    personal = text.split("if ($method === 'GET' && $route === 'guest-requests')", 1)[1].split("if ($method === 'GET' && $route === 'my-orders')", 1)[0]
    admin = text.split("if (in_array($route, ['food-menu/guest-requests'", 1)[1].split("if ($method === 'GET' && $route === 'food-menu/status')", 1)[0]
    assert 'food_guest_request_list($user)' in personal
    assert 'food_guest_request_list($user, true, true)' in admin
    assert 'food_order_require_permission($user' in admin
    assert '!food_guest_request_is_reviewer($user)' in admin
    assert "$filters[] = 'r.created_by = ?'" in text
    assert '|| (!$ownerOnly && !food_guest_request_is_reviewer($user))' in text
    for name in ('cancel', 'update', 'delete'):
        assert f'food_guest_request_{name}($user' in personal
        assert f'food_guest_request_{name}($user' in admin
        assert 'false)' in admin


def test_guest_assets_cache_busted_and_css_mirrored():
    text = (ROOT / 'food-order.php').read_text(encoding='utf-8')
    assert 'assets/food-order.css?v=18' in text
    assert 'assets/food-order-guest.js?v=7' in text
    assert (ROOT / 'assets/food-order.css').read_bytes() == (ROOT / 'food-ticket-web/assets/food-order.css').read_bytes()
