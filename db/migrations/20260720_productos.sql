ALTER TABLE `productos`
  ADD COLUMN `habilitado` tinyint(1) NOT NULL DEFAULT 1 AFTER `updateDate`;
