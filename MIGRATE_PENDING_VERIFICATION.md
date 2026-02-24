# Pending Verification / Visit photo – migrations

500 error **Pending Verifications** page par tab aa sakta hai jab ye migrations run nahi hue hon.

## Server par run karo

```bash
cd /path/to/builder-platform   # apna project path
php artisan migrate
```

## Kaun si migrations lagti hain

| Migration | Kya add karti hai |
|-----------|--------------------|
| `add_visit_verification_fields_to_leads_table` | `leads.visit_photo_path`, `verification_reject_reason` |
| `add_three_track_status_to_leads_table` | `leads.visit_status`, `verification_status`, `sales_status`, `last_verified_visit_at` |
| `create_visit_schedules_table` | Table `visit_schedules` (QR/scheduled visits, `lead_id` bhi) |
| `create_visit_check_ins_table` | Table `visit_check_ins` (check-in + photo + verification_status) |

Agar ye tables/columns missing honge to **Pending Verifications** page 500 dega.

## Already migrate kar chuke ho?

- `php artisan migrate` sirf **pending** migrations chalata hai, purani dubara nahi chalegi.
- Status dekhne ke liye: `php artisan migrate:status`
