-- Porte de arma: boolean -> código textual
ALTER TABLE sigmat.servidor
    ALTER COLUMN porte_arma DROP DEFAULT;

ALTER TABLE sigmat.servidor
    ALTER COLUMN porte_arma TYPE varchar(50)
    USING CASE
        WHEN porte_arma IS TRUE THEN 'SIM'
        ELSE NULL
    END;

ALTER TABLE sigmat.servidor
    ALTER COLUMN porte_arma DROP NOT NULL;
