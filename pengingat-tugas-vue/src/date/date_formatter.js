const dateFormater = (date) => {
  // Ubah tanggal menjadi objek Date
  const formattedDate = new Date(date);

  // Daftar nama bulan dalam bahasa Indonesia
  const monthNames = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];

  // Ambil tanggal, bulan, dan tahun
  const day = String(formattedDate.getDate()).padStart(2, "0");
  const monthIndex = formattedDate.getMonth();
  const year = formattedDate.getFullYear();

  // Ambil jam, menit, dan detik
  const hour = String(formattedDate.getHours()).padStart(2, "0");
  const minute = String(formattedDate.getMinutes()).padStart(2, "0");
  const second = String(formattedDate.getSeconds()).padStart(2, "0");

  // Gabungkan menjadi format yang diinginkan
  const formattedDateTime = `${day} ${monthNames[monthIndex]} ${year}, ${hour}:${minute}:${second}`;

  return formattedDateTime;
};

export default dateFormater;
