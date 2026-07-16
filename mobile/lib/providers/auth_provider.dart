import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  Map<String, dynamic>? _user;
  bool _loading = false;
  String? _error;

  Map<String, dynamic>? get user => _user;
  bool get loading => _loading;
  String? get error => _error;
  bool get isLoggedIn => _user != null;

  Future<bool> login(String email, String password) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final res = await ApiService.post('/login', {
        'email': email,
        'password': password,
      });
      await ApiService.saveToken(res['token']);
      _user = res['user'];
      _loading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _loading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Koneksi gagal. Pastikan server menyala.';
      _loading = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register(String name, String email, String password,
      String passwordConfirmation) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final res = await ApiService.post('/register', {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });
      await ApiService.saveToken(res['token']);
      _user = res['user'];
      _loading = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _loading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Koneksi gagal. Pastikan server menyala.';
      _loading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchUser() async {
    try {
      final res = await ApiService.get('/user', auth: true);
      _user = res;
      notifyListeners();
    } catch (_) {}
  }

  Future<void> logout() async {
    try {
      await ApiService.post('/logout', {}, auth: true);
    } catch (_) {}
    await ApiService.removeToken();
    _user = null;
    notifyListeners();
  }

  Future<bool> checkLogin() async {
    final token = await ApiService.getToken();
    if (token != null) {
      await fetchUser();
      return _user != null;
    }
    return false;
  }
}
