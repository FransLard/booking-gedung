import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class NetworkImageLoader extends StatefulWidget {
  final String url;
  final double? width;
  final double? height;
  final BoxFit fit;

  const NetworkImageLoader({
    super.key,
    required this.url,
    this.width,
    this.height,
    this.fit = BoxFit.cover,
  });

  @override
  State<NetworkImageLoader> createState() => _NetworkImageLoaderState();
}

class _NetworkImageLoaderState extends State<NetworkImageLoader> {
  Uint8List? _bytes;
  bool _loading = true;
  bool _error = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void didUpdateWidget(NetworkImageLoader oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url) {
      setState(() { _bytes = null; _loading = true; _error = false; });
      _load();
    }
  }

  Future<void> _load() async {
    try {
      final resp = await http.get(Uri.parse(widget.url));
      if (resp.statusCode == 200) {
        if (!mounted) return;
        setState(() { _bytes = resp.bodyBytes; _loading = false; });
      } else {
        if (!mounted) return;
        setState(() { _error = true; _loading = false; });
      }
    } catch (_) {
      if (!mounted) return;
      setState(() { _error = true; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return Container(
        width: widget.width,
        height: widget.height,
        color: Colors.grey.shade100,
        child: const Center(child: CircularProgressIndicator(strokeWidth: 2)),
      );
    }
    if (_error || _bytes == null) {
      return Container(
        width: widget.width,
        height: widget.height,
        color: Colors.grey.shade100,
        child: const Center(
          child: Icon(Icons.broken_image_outlined, size: 48, color: Colors.grey),
        ),
      );
    }
    return Image.memory(
      _bytes!,
      width: widget.width,
      height: widget.height,
      fit: widget.fit,
      errorBuilder: (_, __, ___) => Container(
        width: widget.width,
        height: widget.height,
        color: Colors.grey.shade100,
        child: const Center(
          child: Icon(Icons.broken_image_outlined, size: 48, color: Colors.grey),
        ),
      ),
    );
  }
}
