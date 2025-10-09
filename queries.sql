-- 1.1
SELECT  e.event_id, e.title, v.name AS venue_name, e.start_at,
        COUNT(t.ticket_id) AS total_tickets,
        SUM(CASE WHEN t.status = 'AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets
FROM Event e
JOIN Venue v  ON v.venue_id = e.venue_id
JOIN Ticket t ON t.event_id = e.event_id
WHERE e.start_at BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59'
  AND e.title LIKE '%'
GROUP BY e.event_id, e.title, v.name, e.start_at
HAVING available_tickets >= 5
ORDER BY e.start_at;

-- 1.2
SELECT  s.seat_id, s.section, s.row_label, s.seat_number, s.seat_label,
        COALESCE(t.status, 'NO_TICKET_DEFINED') AS ticket_status,
        t.ticket_id, t.price, t.qr_code,
        o.order_id, u.user_id, u.email, u.name
FROM Event e
JOIN Venue v    ON v.venue_id = e.venue_id
JOIN Seat s     ON s.venue_id = v.venue_id
LEFT JOIN Ticket t   ON t.event_id = e.event_id AND t.seat_id = s.seat_id
LEFT JOIN `Order` o  ON o.order_id = t.order_id
LEFT JOIN `User`  u  ON u.user_id = o.user_id
WHERE e.event_id = 1
ORDER BY s.section, s.row_label, s.seat_number;

-- 1.3
SELECT  e.event_id, e.title, e.start_at,
        COUNT(*) AS total_tickets,
        SUM(CASE WHEN t.status = 'TICKETED' THEN 1 ELSE 0 END) AS sold_tickets,
        SUM(CASE WHEN t.status = 'TICKETED' THEN 1 ELSE 0 END) / COUNT(*) AS sellthrough_ratio
FROM Event e
JOIN Ticket t ON t.event_id = e.event_id
GROUP BY e.event_id, e.title, e.start_at
HAVING sellthrough_ratio < 0.8
ORDER BY e.start_at;

-- 2.1
SELECT  o.order_id, o.created_at, o.status, o.payment_status,
        COUNT(t.ticket_id) AS ticket_count,
        GROUP_CONCAT(DISTINCT v.name ORDER BY v.name SEPARATOR ', ') AS venues
FROM `Order` o
JOIN Ticket t ON t.order_id = o.order_id
JOIN Event e  ON e.event_id = t.event_id
JOIN Venue v  ON v.venue_id = e.venue_id
WHERE o.user_id = 1
GROUP BY o.order_id, o.created_at, o.status, o.payment_status
ORDER BY o.created_at DESC;

-- 2.2
SELECT  t.ticket_id, t.price, s.seat_label, e.title, e.start_at, v.name AS venue_name
FROM Ticket t
JOIN `Order` o ON o.order_id = t.order_id
JOIN Event e   ON e.event_id = t.event_id
JOIN Seat s    ON s.seat_id = t.seat_id
JOIN Venue v   ON v.venue_id = e.venue_id
WHERE o.order_id = 1
ORDER BY e.start_at, s.seat_label;

-- 2.3
SELECT  e.event_id, e.title, e.start_at,
        SUM(t.price) AS gross_revenue,
        COUNT(*)     AS tickets_count
FROM Event e
JOIN Ticket t  ON t.event_id = e.event_id
JOIN `Order` o ON o.order_id = t.order_id
WHERE t.status = 'TICKETED'
  AND o.status = 'PAID'
  AND o.payment_status = 'SUCCESS'
GROUP BY e.event_id, e.title, e.start_at
ORDER BY e.start_at;

-- 3.1
SELECT  e.event_id, e.title, e.start_at,
        COUNT(t.ticket_id) AS total_tickets,
        SUM(CASE WHEN t.status = 'AVAILABLE' THEN 1 ELSE 0 END) AS available_tickets,
        COALESCE(w.wl_size, 0) AS waitlist_size
FROM Event e
JOIN Ticket t ON t.event_id = e.event_id
LEFT JOIN (
  SELECT event_id, COUNT(*) AS wl_size
  FROM Waitlist
  WHERE status IN ('ACTIVE','OFFERED')
  GROUP BY event_id
) w ON w.event_id = e.event_id
GROUP BY e.event_id, e.title, e.start_at, w.wl_size
HAVING available_tickets = 0
ORDER BY e.start_at;

-- 3.2
SELECT  wl.entry_id, wl.created_at, wl.seats_requested,
        u.user_id, u.email, u.name
FROM Waitlist wl
JOIN `User` u ON u.user_id = wl.user_id
WHERE wl.event_id = 1
  AND wl.status = 'ACTIVE'
ORDER BY wl.created_at ASC
LIMIT 1;

-- 3.3
SELECT  t.ticket_id, s.seat_label, t.hold_expires_at
FROM Ticket t
JOIN Seat s ON s.seat_id = t.seat_id
WHERE t.event_id = 1
  AND t.status = 'HELD'
  AND t.hold_expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 MINUTE)
ORDER BY t.hold_expires_at ASC;

-- 4.1
SELECT  e.category,
        COUNT(DISTINCT e.event_id) AS events_count,
        COUNT(t.ticket_id)         AS tickets_created,
        SUM(CASE WHEN t.status = 'TICKETED' THEN 1 ELSE 0 END) AS tickets_sold
FROM Event e
LEFT JOIN Ticket t ON t.event_id = e.event_id
WHERE e.start_at BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59'
GROUP BY e.category
HAVING events_count > 0
ORDER BY tickets_sold DESC, e.category;

-- 4.2
SELECT  v.venue_id, v.name AS venue_name,
        COUNT(t.ticket_id) AS tickets_created,
        SUM(CASE WHEN t.status = 'TICKETED' THEN 1 ELSE 0 END) AS tickets_sold
FROM Venue v
JOIN Event e  ON e.venue_id = v.venue_id
JOIN Ticket t ON t.event_id = e.event_id
WHERE e.start_at BETWEEN '2025-01-01 00:00:00' AND '2025-12-31 23:59:59'
GROUP BY v.venue_id, v.name
HAVING tickets_created > 0
ORDER BY tickets_sold DESC, v.name;

-- 4.3
SELECT  t.ticket_id, e.title, s.seat_label,
        o.order_id, o.status AS order_status, o.payment_status
FROM Ticket t
JOIN Event e   ON e.event_id = t.event_id
JOIN Seat s    ON s.seat_id = t.seat_id
LEFT JOIN `Order` o ON o.order_id = t.order_id
WHERE t.status = 'TICKETED'
  AND (o.order_id IS NULL OR o.status <> 'PAID' OR o.payment_status <> 'SUCCESS')
ORDER BY e.start_at, s.seat_label;

