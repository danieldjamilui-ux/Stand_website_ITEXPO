import pymysql  # Ganti mysql.connector dengan pymysql
import json
import sys
from pymysql import cursors

class ContentBasedFiltering:
    def __init__(self, host, user, password, database):
        # Inisialisasi koneksi database dengan PyMySQL
        self.conn = pymysql.connect(
            host=host,
            user=user,
            password=password,
            database=database,
            cursorclass=cursors.DictCursor  # Untuk mendapatkan hasil sebagai dictionary
        )
        self.cursor = self.conn.cursor()
    
    def get_user_skin_profile(self, user_id):
        # Mendapatkan profil kulit pengguna
        query = """SELECT usp.*, st.name as skin_type_name 
                 FROM user_skin_profiles usp 
                 JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                 WHERE usp.user_id = %s 
                 ORDER BY usp.created_at DESC LIMIT 1"""
        self.cursor.execute(query, (user_id,))
        return self.cursor.fetchone()
    
    def get_user_skin_concerns(self, profile_id):
        # Mendapatkan masalah kulit pengguna
        query = """SELECT usc.*, sc.name as concern_name 
                 FROM user_skin_concerns usc 
                 JOIN skin_concerns sc ON usc.concern_id = sc.concern_id 
                 WHERE usc.profile_id = %s"""
        self.cursor.execute(query, (profile_id,))
        return self.cursor.fetchall()
    
    def calculate_skin_type_compatibility(self, product_id, skin_type_id):
        # Menghitung kesesuaian produk dengan jenis kulit
        query = """SELECT compatibility_score 
                 FROM product_skin_type_compatibility 
                 WHERE product_id = %s AND skin_type_id = %s"""
        self.cursor.execute(query, (product_id, skin_type_id))
        result = self.cursor.fetchone()
        # Konversi ke float untuk menghindari error decimal.Decimal
        return float(result['compatibility_score']) if result else 0.0
    
    def calculate_skin_concern_compatibility(self, product_id, skin_concerns):
        # Menghitung kesesuaian produk dengan masalah kulit
        if not skin_concerns:
            return 0.0
        
        total_score = 0.0
        total_weight = 0.0
        
        for concern in skin_concerns:
            query = """SELECT effectiveness_score 
                     FROM product_skin_concern_compatibility 
                     WHERE product_id = %s AND concern_id = %s"""
            self.cursor.execute(query, (product_id, concern['concern_id']))
            result = self.cursor.fetchone()
            
            if result:
                # Gunakan tingkat keparahan sebagai bobot
                weight = float(concern['severity'])  # Konversi ke float
                effectiveness = float(result['effectiveness_score'])  # Konversi ke float
                total_score += effectiveness * weight
                total_weight += weight
        
        return total_score / total_weight if total_weight > 0 else 0.0
    
    def generate_recommendations(self, user_id, profile_id):
        # Fungsi utama untuk menghasilkan rekomendasi
        # Dapatkan profil kulit user
        skin_profile = self.get_user_skin_profile(user_id)
        if not skin_profile:
            return None
        
        # Dapatkan masalah kulit user
        skin_concerns = self.get_user_skin_concerns(profile_id)
        
        # Dapatkan semua produk
        self.cursor.execute("SELECT * FROM skincare_products")
        products = self.cursor.fetchall()
        
        recommendations = []
        
        for product in products:
            # Hitung skor kesesuaian dengan jenis kulit
            skin_type_score = self.calculate_skin_type_compatibility(
                product['product_id'], skin_profile['skin_type_id'])
            
            # Hitung skor kesesuaian dengan masalah kulit
            skin_concern_score = self.calculate_skin_concern_compatibility(
                product['product_id'], skin_concerns)
            
            # Hitung skor total (rata-rata tertimbang)
            # Bobot: 40% jenis kulit, 60% masalah kulit
            total_score = (0.4 * skin_type_score) + (0.6 * skin_concern_score)
            
            recommendations.append({
                'product_id': product['product_id'],
                'similarity_score': total_score
            })
        
        # Urutkan rekomendasi berdasarkan skor (dari tertinggi ke terendah)
        recommendations.sort(key=lambda x: x['similarity_score'], reverse=True)
        
        # Simpan rekomendasi ke database
        return self.save_recommendations(user_id, profile_id, recommendations)
    
    def save_recommendations(self, user_id, profile_id, recommendations):
        # Simpan rekomendasi ke database
        try:
            # Nonaktifkan autocommit untuk kontrol transaksi manual
            self.conn.autocommit = False
            
            # Simpan header rekomendasi
            query = "INSERT INTO recommendations (user_id, profile_id) VALUES (%s, %s)"
            self.cursor.execute(query, (user_id, profile_id))
            recommendation_id = self.cursor.lastrowid
            
            # Simpan detail rekomendasi (semua produk)
            rank = 1
            for recommendation in recommendations:  # Hapus [:10] untuk menampilkan semua produk
                query = """INSERT INTO recommendation_details 
                    (`recommendation_id`, `product_id`, `similarity_score`, `rank`) 
                    VALUES (%s, %s, %s, %s)"""

                self.cursor.execute(query, (
                    recommendation_id,
                    recommendation['product_id'],
                    recommendation['similarity_score'],
                    rank
                ))
                rank += 1
            
            # Commit transaksi
            self.conn.commit()
            return recommendation_id
        except Exception as e:
            # Rollback jika terjadi error
            self.conn.rollback()
            print(f"Error: {str(e)}", file=sys.stderr)
            return None
        finally:
            # Kembalikan autocommit ke True
            self.conn.autocommit = True
    
    def close(self):
        # Tutup koneksi database
        self.cursor.close()
        self.conn.close()

# Fungsi utama untuk dijalankan dari PHP
def main():
    # Baca parameter dari stdin (dikirim oleh PHP)
    input_data = json.loads(sys.stdin.read())
    
    # Ambil parameter
    host = input_data.get('host', 'localhost')
    user = input_data.get('user', 'root')
    password = input_data.get('password', '')
    database = input_data.get('database', 'skincare_recommendation')
    user_id = input_data.get('user_id')
    profile_id = input_data.get('profile_id')
    
    # Inisialisasi CBF
    cbf = ContentBasedFiltering(host, user, password, database)
    
    # Generate rekomendasi
    recommendation_id = cbf.generate_recommendations(user_id, profile_id)
    
    # Tutup koneksi
    cbf.close()
    
    # Kirim hasil kembali ke PHP
    result = {
        'success': recommendation_id is not None,
        'recommendation_id': recommendation_id
    }
    print(json.dumps(result))

# Jalankan fungsi utama jika file ini dijalankan langsung
if __name__ == "__main__":
    main()