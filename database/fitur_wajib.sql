ALTER SESSION SET CURRENT_SCHEMA = UAS;

-- Tambahkan kolom TOTAL_SKS jika belum ada
BEGIN
  EXECUTE IMMEDIATE 'ALTER TABLE MAHASISWA ADD (TOTAL_SKS NUMBER DEFAULT 0)';
EXCEPTION
  WHEN OTHERS THEN NULL;
END;
/

-- Trigger update otomatis SKS
CREATE OR REPLACE TRIGGER trg_update_sks_mahasiswa
AFTER INSERT OR DELETE ON NILAI
FOR EACH ROW
DECLARE
  v_total NUMBER;
  v_mhs_id NUMBER;
BEGIN
  IF INSERTING THEN
    v_mhs_id := :NEW.MAHASISWA_ID;
  ELSE
    v_mhs_id := :OLD.MAHASISWA_ID;
  END IF;

  SELECT NVL(SUM(mk.SKS), 0)
  INTO v_total
  FROM NILAI n
  JOIN MATA_KULIAH mk ON n.MATKUL_ID = mk.MATKUL_ID
  WHERE n.MAHASISWA_ID = v_mhs_id;

  UPDATE MAHASISWA
  SET TOTAL_SKS = v_total
  WHERE MAHASISWA_ID = v_mhs_id;
END;
/
SHOW ERRORS;


------------------------------------------------------------
-- 2️⃣ VIEW : GABUNGAN DOSEN, MATA KULIAH, DAN JUMLAH KEHADIRAN
------------------------------------------------------------

CREATE OR REPLACE VIEW v_dosen_matkul_kehadiran AS
SELECT 
    d.NAMA_LENGKAP AS NAMA_DOSEN,
    mk.NAMA_MATKUL,
    COUNT(a.ABSENSI_ID) AS TOTAL_KEHADIRAN
FROM ABSENSI a
JOIN JADWAL_KULIAH jk ON a.JADWAL_ID = jk.JADWAL_ID
JOIN MATA_KULIAH mk ON jk.MATKUL_ID = mk.MATKUL_ID
JOIN DOSEN d ON mk.DOSEN_ID = d.DOSEN_ID
WHERE a.STATUS_KEHADIRAN = 'Hadir'
GROUP BY d.NAMA_LENGKAP, mk.NAMA_MATKUL;
/
SHOW ERRORS;


------------------------------------------------------------
-- 3️⃣ PROCEDURE : REKAP NILAI & STATUS KELULUSAN OTOMATIS
------------------------------------------------------------

CREATE OR REPLACE PROCEDURE sp_rekap_nilai(
    p_mahasiswa_id IN NUMBER,
    p_matkul_id IN NUMBER
) AS
    v_akhir  NUMBER;
    v_grade  VARCHAR2(2);
    v_status VARCHAR2(15);
BEGIN
    -- Hitung nilai akhir berdasarkan bobot
    SELECT (NILAI_TUGAS * 0.3 + NILAI_UTS * 0.3 + NILAI_UAS * 0.4)
    INTO v_akhir
    FROM NILAI
    WHERE MAHASISWA_ID = p_mahasiswa_id
      AND MATKUL_ID = p_matkul_id;

    -- Tentukan grade
    IF v_akhir >= 85 THEN v_grade := 'A';
    ELSIF v_akhir >= 75 THEN v_grade := 'B';
    ELSIF v_akhir >= 65 THEN v_grade := 'C';
    ELSIF v_akhir >= 50 THEN v_grade := 'D';
    ELSE v_grade := 'E';
    END IF;

    -- Tentukan status kelulusan
    IF v_akhir >= 65 THEN v_status := 'Lulus';
    ELSE v_status := 'Tidak Lulus';
    END IF;

    -- Update tabel nilai
    UPDATE NILAI
    SET NILAI_AKHIR = v_akhir,
        GRADE = v_grade,
        STATUS = v_status,
        UPDATED_AT = SYSDATE
    WHERE MAHASISWA_ID = p_mahasiswa_id
      AND MATKUL_ID = p_matkul_id;

    COMMIT;
END;
/
SHOW ERRORS;


------------------------------------------------------------
-- 4️⃣ INDEX UNTUK EFISIENSI QUERY
------------------------------------------------------------

CREATE INDEX idx_mahasiswa_nim ON MAHASISWA (NIM);
CREATE INDEX idx_matkul_kode ON MATA_KULIAH (KODE_MATKUL);
/

------------------------------------------------------------
-- SELESAI ✅
------------------------------------------------------------
PROMPT SmartCampus fitur wajib berhasil dibuat!
