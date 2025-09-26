<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel {

    /**
     * Tìm user theo ID (trả về 1 dòng hoặc false)
     */
    public function findUserById($id) {
        $id = (int)$id;
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        return $this->selectOne($sql, [':id' => $id]);
    }

    /**
     * Tìm user theo keyword (name, email, fullname)
     * Escape giá trị tìm kiếm bằng addcslashes để tránh wildcard injection.
     */
    /**
 * Search users by a keyword (safe against SQL injection + handles LIKE wildcards)
 *
 * - Nếu $keyword rỗng => trả về tất cả users (bạn có thể thay đổi để trả mảng rỗng nếu muốn).
 * - Escape %, _ và backslash trong giá trị bằng addcslashes before binding.
 */
public function searchUsers($keyword) {
    $keyword = trim((string)$keyword);

    // Nếu không truyền keyword, trả về toàn bộ
    if ($keyword === '') {
        $sql = 'SELECT * FROM users ORDER BY id DESC';
        return $this->select($sql);
    }

    // Escape wildcard và backslash trong value (để tìm literal % hoặc _ khi user nhập)
    // addcslashes(..., "%_\\") -> % -> \% ; _ -> \_ ; \ -> \\
    $escaped = addcslashes($keyword, "%_\\");
    $like = "%{$escaped}%";

    // Sử dụng placeholder riêng cho từng cột (không dùng cùng :kw nhiều lần)
    $sql = 'SELECT * FROM users
            WHERE name LIKE :kw1
               OR email LIKE :kw2
               OR fullname LIKE :kw3
            ORDER BY id DESC';

    return $this->select($sql, [
        ':kw1' => $like,
        ':kw2' => $like,
        ':kw3' => $like,
    ]);
}



    /**
     * Xác thực user (login)
     */
    public function auth($userName, $password) {
        $userName = trim((string)$userName);
        if ($userName === '' || $password === '') {
            return false;
        }

        $sql = 'SELECT * FROM users WHERE name = :name LIMIT 1';
        $user = $this->selectOne($sql, [':name' => $userName]);

        if ($user && isset($user['password']) && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /**
     * Xóa user theo id (hard delete) - có LIMIT 1
     */
    public function deleteUserById($id) {
        $id = (int)$id;
        $sql = 'DELETE FROM users WHERE id = :id LIMIT 1';
        return $this->delete($sql, [':id' => $id]);
    }

    /**
     * Cập nhật user (giữ password cũ nếu không gửi password mới)
     */
    public function updateUser($input) {
        if (!isset($input['id'])) {
            throw new InvalidArgumentException('Missing user id');
        }
        $id = (int)$input['id'];

        $current = $this->findUserById($id);
        if (!$current) {
            throw new RuntimeException('User not found');
        }

        $name     = trim((string)($input['name'] ?? $current['name'] ?? ''));
        $fullname = trim((string)($input['fullname'] ?? $current['fullname'] ?? ''));
        $email    = trim((string)($input['email'] ?? $current['email'] ?? ''));

        if (!empty($input['password'])) {
            $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        } else {
            $passwordHash = $current['password'];
        }

        $sql = 'UPDATE users
                SET name = :name, fullname = :fullname, email = :email, password = :password
                WHERE id = :id LIMIT 1';

        return $this->update($sql, [
            ':name'     => $name,
            ':fullname' => $fullname,
            ':email'    => $email,
            ':password' => $passwordHash,
            ':id'       => $id
        ]);
    }

    /**
     * Thêm mới user
     */
    public function insertUser($input) {
        $name     = trim((string)($input['name'] ?? ''));
        $fullname = trim((string)($input['fullname'] ?? ''));
        $email    = trim((string)($input['email'] ?? ''));
        $password = $input['password'] ?? '';

        if ($name === '' || $password === '') {
            throw new InvalidArgumentException('Name and password are required');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (name, fullname, email, password)
                VALUES (:name, :fullname, :email, :password)';

        return $this->insert($sql, [
            ':name'     => $name,
            ':fullname' => $fullname,
            ':email'    => $email,
            ':password' => $passwordHash
        ]);
    }

    /**
     * Lấy danh sách users (có thể tìm kiếm bằng keyword)
     */
   public function getUsers($params = []) {
    $sql = 'SELECT * FROM users';
    $bindings = [];

    if (!empty($params['keyword'])) {
        $keyword = trim((string)$params['keyword']);
        if ($keyword !== '') {
            $escaped = addcslashes($keyword, "%_\\");
            $like = "%{$escaped}%";

            // Dùng placeholders khác nhau cho mỗi lần xuất hiện
            $sql .= ' WHERE name LIKE :kw1 OR email LIKE :kw2 OR fullname LIKE :kw3';
            $bindings[':kw1'] = $like;
            $bindings[':kw2'] = $like;
            $bindings[':kw3'] = $like;
        }
    }

    $sql .= ' ORDER BY id DESC';

    if (!empty($bindings)) {
        return $this->select($sql, $bindings);
    }
    return $this->select($sql);
}

}
