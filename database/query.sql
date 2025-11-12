SELECT * FROM USERS;
SELECT * FROM DOSEN;
SELECT * FROM MAHASISWA;
SELECT * FROM MATA_KULIAH;
SELECT * FROM RUANG_KELAS;
SELECT * FROM JADWAL_KULIAH;
SELECT * FROM SESI_ABSENSI;
SELECT * FROM ABSENSI;
SELECT * FROM NILAI;
SELECT * FROM DUAL;
DELETE FROM USERS WHERE USER_ID = '20';
DESC MATA_KULIAH;
INSERT INTO USERS (
    USERNAME,
    PASSWORD,
    EMAIL,
    ROLE,
    IS_ACTIVE,
    CREATED_AT
) VALUES (
    'admin',
    'admin123',
    'admin@smartcampus.ac.id',
    'admin',
    1,
    SYSDATE
);
COMMIT;

-- 1. Tambah user untuk dosen
INSERT INTO USERS (
    USERNAME,
    PASSWORD,
    EMAIL,
    ROLE,
    IS_ACTIVE,
    CREATED_AT
) VALUES (
    'frizzki',
    'dosen123',
    'frizzki@campus.ac.id',
    'dosen',
    1,
    SYSDATE
);

-- 2. Ambil USER_ID dosen dari USERS
DECLARE
    v_user_id NUMBER;
BEGIN
    SELECT USER_ID INTO v_user_id
    FROM USERS
    WHERE USERNAME = 'frizzki';

    -- 3. Tambahkan ke tabel DOSEN
    INSERT INTO DOSEN (
        USER_ID,
        NIP,
        NAMA_LENGKAP,
        EMAIL,
        NO_TELEPON,
        ALAMAT,
        JENIS_KELAMIN,
        TANGGAL_LAHIR,
        CREATED_AT
    ) VALUES (
        v_user_id,
        '198700123',
        'Frizzki Andara',
        'frizzki@campus.ac.id',
        '08123456789',
        'Jl. Cendana No. 5, Surabaya',
        'Laki-laki',
        TO_DATE('1987-03-15', 'YYYY-MM-DD'),
        SYSDATE
    );
END;
/

-- ========== MAHASISWA ==========
-- 1. Tambah user untuk mahasiswa
INSERT INTO USERS (
    USERNAME,
    PASSWORD,
    EMAIL,
    ROLE,
    IS_ACTIVE,
    CREATED_AT
) VALUES (
    'soraa',
    'mhs123',
    'soraa@campus.ac.id',
    'mahasiswa',
    1,
    SYSDATE
);

-- 2. Ambil USER_ID mahasiswa dari USERS
DECLARE
    v_user_id NUMBER;
BEGIN
    SELECT USER_ID INTO v_user_id
    FROM USERS
    WHERE USERNAME = 'soraa';

    -- 3. Tambahkan ke tabel MAHASISWA
    INSERT INTO MAHASISWA (
        USER_ID,
        NIM,
        NAMA_LENGKAP,
        EMAIL,
        NO_TELEPON,
        ALAMAT,
        JENIS_KELAMIN,
        ANGKATAN,
        SEMESTER,
        STATUS,
        CREATED_AT
    ) VALUES (
        v_user_id,
        '210202001',
        'Ryugen S. Soraa',
        'soraa@campus.ac.id',
        '082312345678',
        'Jl. Sakura No. 9, Bandung',
        'Laki-laki',
        2021,
        5,
        'Aktif',
        SYSDATE
    );
END;
/

INSERT INTO RUANG_KELAS (RUANG_ID, KODE_RUANG, NAMA_RUANG, KAPASITAS, LOKASI, CREATED_AT)
VALUES (SEQ_RUANG.NEXTVAL, 'R101', 'Ruang Teori 1', 34, 'Gedung A Lantai 1', SYSDATE);

INSERT INTO RUANG_KELAS (RUANG_ID, KODE_RUANG, NAMA_RUANG, KAPASITAS, LOKASI, CREATED_AT)
VALUES (SEQ_RUANG.NEXTVAL, 'R102', 'Ruang Teori 2', 35, 'Gedung A Lantai 1', SYSDATE);

INSERT INTO RUANG_KELAS (RUANG_ID, KODE_RUANG, NAMA_RUANG, KAPASITAS, LOKASI, CREATED_AT)
VALUES (SEQ_RUANG.NEXTVAL, 'LAB201', 'Lab Komputer 1', 25, 'Gedung B Lantai 2', SYSDATE);

INSERT INTO RUANG_KELAS (RUANG_ID, KODE_RUANG, NAMA_RUANG, KAPASITAS, LOKASI, CREATED_AT)
VALUES (SEQ_RUANG.NEXTVAL, 'LAB202', 'Lab Komputer 2', 25, 'Gedung B Lantai 2', SYSDATE);

CREATE OR REPLACE TRIGGER trg_update_sks_mahasiswa
AFTER INSERT OR DELETE ON NILAI
DECLARE
  v_total NUMBER;
BEGIN
  -- Loop semua mahasiswa yang berubah
  FOR r IN (SELECT DISTINCT MAHASISWA_ID FROM NILAI) LOOP
    SELECT NVL(SUM(mk.SKS), 0)
    INTO v_total
    FROM NILAI n
    JOIN MATA_KULIAH mk ON n.MATKUL_ID = mk.MATKUL_ID
    WHERE n.MAHASISWA_ID = r.MAHASISWA_ID;

    UPDATE MAHASISWA
    SET TOTAL_SKS = v_total
    WHERE MAHASISWA_ID = r.MAHASISWA_ID;
  END LOOP;
END;
/
SHOW ERRORS;
SELECT COLUMN_NAME FROM ALL_TAB_COLUMNS 
WHERE TABLE_NAME = 'SESI_ABSENSI';

SELECT TABLE_NAME, COLUMN_NAME 
FROM ALL_TAB_COLUMNS 
WHERE TABLE_NAME IN ('ABSENSI', 'SESI_ABSENSI') 
AND OWNER = 'UAS'
ORDER BY TABLE_NAME, COLUMN_ID;

DESC UAS.SESI_ABSENSI;
DESC UAS.ABSENSI;

ALTER TABLE ABSENSI ADD (SESI_ID NUMBER REFERENCES SESI_ABSENSI(SESI_ID) ON DELETE CASCADE);
COMMIT;
DESC ABSENSI;
