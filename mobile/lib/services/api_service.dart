import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String get baseUrl {
    if (Platform.isAndroid) {
      return 'http://10.0.2.2:8000/api';
    }
    return 'http://localhost:8000/api';
  }

  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  static Future<void> removeToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  static Future<Map<String, String>> _headers({bool auth = false, bool json = false}) async {
    final headers = <String, String>{
      'Accept': 'application/json',
    };
    if (json) {
      headers['Content-Type'] = 'application/json';
    }
    if (auth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  static Future<dynamic> post(
    String path,
    Map<String, dynamic> body, {
    bool auth = false,
  }) async {
    final uri = Uri.parse('$baseUrl$path');
    try {
      final response = await http.post(
        uri,
        headers: await _headers(auth: auth, json: true),
        body: jsonEncode(body),
      );
      return _handle(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(
        message: 'Gagal terhubung ke server ($uri)',
        statusCode: 0,
      );
    }
  }

  static Future<dynamic> postMultipart(
    String path,
    Map<String, dynamic> fields,
    String fileField,
    File file, {
    bool auth = false,
  }) async {
    final request = http.MultipartRequest(
      'POST',
      Uri.parse('$baseUrl$path'),
    );
    request.headers.addAll(await _headers(auth: auth));
    request.fields.addAll(fields.map((k, v) => MapEntry(k, v.toString())));
    request.files.add(await http.MultipartFile.fromPath(fileField, file.path));
    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    return _handle(response);
  }

  static Future<dynamic> get(
    String path, {
    bool auth = false,
    Map<String, String>? queryParams,
  }) async {
    final uri =
        Uri.parse('$baseUrl$path').replace(queryParameters: queryParams);
    try {
      final response = await http.get(
        uri,
        headers: await _headers(auth: auth),
      );
      return _handle(response);
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(
        message: 'Gagal terhubung ke server ($uri)',
        statusCode: 0,
      );
    }
  }

  static Future<dynamic> patch(
    String path, {
    bool auth = false,
    Map<String, dynamic>? body,
  }) async {
    final response = await http.patch(
      Uri.parse('$baseUrl$path'),
      headers: await _headers(auth: auth, json: true),
      body: body != null ? jsonEncode(body) : null,
    );
    return _handle(response);
  }

  static dynamic _handle(http.Response response) {
    dynamic body;
    try {
      body = jsonDecode(response.body);
    } catch (_) {
      throw ApiException(
        message: 'Server error (${response.statusCode})',
        statusCode: response.statusCode,
      );
    }
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }
    throw ApiException(
      message: body['message'] ?? 'Terjadi kesalahan',
      statusCode: response.statusCode,
      errors: body['errors'],
    );
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;
  final dynamic errors;

  ApiException({
    required this.message,
    required this.statusCode,
    this.errors,
  });

  @override
  String toString() => message;
}
