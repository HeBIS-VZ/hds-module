TRUNCATE static_post;
INSERT INTO static_post (pid, language, headline, nav_title, content, author, visible) VALUES
  (11, 'en', 'TestTestTestTestTest-1', 'Test-1', '<p>Hallo <b>Test-1</b></p>', 'T1', 1),
  (11, 'de', 'ProbeProbeProbe-1', 'Probe-1', '<p>Hallo <b>Probe-1</b></p>', 'T1', 1),
  (12, 'en', 'TestTestTestTestTest-2', 'Test-2', '<p>Hallo <b>Test-2</b></p>', 'T1', 1),
  (12, 'de', 'ProbeProbeProbe-2', 'Probe-2', '<p>Hallo <b>Probe-2</b></p>', 'T1', 1),
  (13, 'en', 'TestTestTestTestTest-3', 'Test-3', '<p>Hallo <b>Test-3</b></p>', 'T1', 1),
  (13, 'de', 'ProbeProbeProbe-3', 'Probe-3', '<p>Hallo <b>Probe-3</b></p>', 'T1', 1);


TRUNCATE broadcasts;
INSERT INTO broadcasts (bcid, language, message, type, startDate, expireDate, hide) VALUES
  (1, 'en', 'Some Message 1', 'warning', now(), date_add(startDate, INTERVAL 30 DAY), 1),
  (1, 'de', 'Irgendeine Nachricht 1', 'warning', now(), date_add(startDate, INTERVAL 30 DAY), 1),
  (2, 'en', 'Some Message 2', 'info', now(), '2017-12-26 00:00:00', 1),
  (2, 'de', 'Irgendeine Nachricht 2', 'info', now(), '2017-12-26 00:00:00', 1),
  (3, 'en', 'Some Message 3', 'info', now(), date_add(startDate, INTERVAL 5 DAY), 0),
  (3, 'de', 'Irgendeine Nachricht 3', 'info', now(), date_add(startDate, INTERVAL 5 DAY), 0);
