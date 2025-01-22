<?php
require_once 'DAO.php';
class Member
{
    public int $memberid; //会員ID
    public string $email; //メールアドレス
    public string $membername; //会員名
    public string $password; //パスワード
    public int $flag; //ユーザ権限

}
class MemberDAO
{
    //DBからメールアドレスとパスワードが一致する会員データを取得する
    public function get_member(string $email, string $password): bool|object
    {
        $dbh = DAO::get_db_connect(); //DB接続
        $sql = "SELECT * FROM member WHERE email = :email";
        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->execute();

        //１件分のデータをmemberクラスのオブジェクトとして取得する
        $member = $stmt->fetchObject('member');

        //会員データが取得できたとき
        if ($member !== false) {
            //パスワードが一致するか検証
            if (password_verify($password, $member->password)) {
                //会員データを返す
                return $member;
            }
        }

        return false;
    }
    //P.111 会員データを登録する
    // public function insert(Member $member)
    // {
    //     $dbh = DAO::get_db_connect(); //DB接続
    //     $sql="INSERT INTO member(email,membername,zipcode,address,tel,password) 
    //             VALUES(:email,:membername,:zipcode,:address,:tel,:password)";
    //     $stmt=$dbh->prepare($sql);

    //     $password=password_hash($member->password,PASSWORD_DEFAULT);

    //     $stmt->bindValue(':email',$member->email,PDO::PARAM_STR);
    //     $stmt->bindValue(':membername',$member->membername,PDO::PARAM_STR);
    //     $stmt->bindValue(':zipcode',$member->zipcode,PDO::PARAM_STR);
    //     $stmt->bindValue(':address',$member->address,PDO::PARAM_STR);
    //     $stmt->bindValue(':tel',$member->tel,PDO::PARAM_STR);
    //     $stmt->bindValue(':password',$password,PDO::PARAM_STR);
    //     $stmt->execute();
    // }
    //P.114 指定したメールアドレスの会員のデータが存在すればtrueを返す
    public function email_exists(string $email)
    {
        $dbh = DAO::get_db_connect(); //DB接続
        $sql = "SELECT * FROM member WHERE email=:email";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch() !== false) {
            return true;
        } else {
            return false;
        }
    }
}
