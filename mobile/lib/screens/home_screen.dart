import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';
import 'package:shimmer/shimmer.dart';
import '../models/gedung.dart';
import '../providers/auth_provider.dart';
import '../services/api_service.dart';
import '../widgets/network_image_loader.dart';
import '../widgets/notification_bell.dart';
import '../theme/app_theme.dart';
import 'login_screen.dart';
import 'gedung_detail_screen.dart';
import 'my_bookings_screen.dart';
import 'profile_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Gedung> _list = [];
  bool _loading = true;
  String? _error;
  final _searchCtrl = TextEditingController();
  List<Gedung> get _filtered => _searchCtrl.text.isEmpty
      ? _list
      : _list.where((g) => g.nama.toLowerCase().contains(_searchCtrl.text.toLowerCase())).toList();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() { _loading = true; _error = null; });
    try {
      final res = await ApiService.get('/gedung');
      _list = (res as List<dynamic>)
          .map((e) => Gedung.fromJson(e as Map<String, dynamic>))
          .toList();
      setState(() => _loading = false);
    } catch (_) {
      setState(() { _error = 'Gagal memuat data'; _loading = false; });
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Scaffold(
      body: Column(
        children: [
          _buildHeader(auth),
          Expanded(
            child: _buildBody(),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(AuthProvider auth) {
    return Container(
      padding: EdgeInsets.only(
        left: 20,
        right: 12,
        top: MediaQuery.of(context).padding.top + 8,
        bottom: 20,
      ),
      decoration: AppTheme.premiumHeader,
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 4),
                    Text(
                      'Halo${auth.isLoggedIn ? ', ${auth.user?['name'] ?? ''}' : ''}!',
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Cari tempat untuk acara Anda',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.white.withValues(alpha: 0.6),
                        letterSpacing: 0.3,
                      ),
                    ),
                  ],
                ),
              ),
              if (auth.isLoggedIn) ...[
                Container(
                  margin: const EdgeInsets.only(left: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  padding: const EdgeInsets.all(10),
                  child: const NotificationBell(iconSize: 20),
                ),
                _headerIcon(Icons.person_rounded, () => Navigator.push(context, _slideRight(const ProfileScreen()))),
                _headerIcon(Icons.list_alt_rounded, () => Navigator.push(context, _slideRight(const MyBookingsScreen()))),
                _headerIcon(Icons.logout_rounded, () async {
                  await auth.logout();
                  if (!context.mounted) return;
                  Navigator.pushReplacement(context, _fadeReplace(const LoginScreen()));
                }),
              ],
            ],
          ),
          const SizedBox(height: 14),
          Container(
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.white.withValues(alpha: 0.08)),
            ),
            child: TextField(
              controller: _searchCtrl,
              onChanged: (_) => setState(() {}),
              style: const TextStyle(color: Colors.white, fontSize: 15),
              decoration: InputDecoration(
                hintText: 'Cari gedung...',
                hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.4)),
                prefixIcon: Icon(Icons.search_rounded,
                    color: Colors.white.withValues(alpha: 0.5), size: 22),
                suffixIcon: _searchCtrl.text.isNotEmpty
                    ? IconButton(
                        icon: Icon(Icons.close_rounded,
                            color: Colors.white.withValues(alpha: 0.5)),
                        onPressed: () {
                          _searchCtrl.clear();
                          setState(() {});
                        },
                      )
                    : null,
                border: InputBorder.none,
                filled: false,
                contentPadding: const EdgeInsets.symmetric(vertical: 14),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _headerIcon(IconData icon, VoidCallback onTap) {
    return Container(
      margin: const EdgeInsets.only(left: 4),
      child: Material(
        color: Colors.white.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.all(10),
            child: Icon(icon, color: Colors.white, size: 20),
          ),
        ),
      ),
    );
  }

  Widget _buildBody() {
    if (_loading) return _buildShimmer();
    if (_error != null) {
      return Center(
        child: Padding(
          padding: AppTheme.screenPadding,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Icon(Icons.wifi_off_rounded, size: 48, color: Colors.red.shade300),
              ),
              const SizedBox(height: 16),
              const Text('Gagal memuat data',
                  style: TextStyle(fontSize: 16, color: AppTheme.textSecondary)),
              const SizedBox(height: 4),
              Text('Periksa koneksi Anda',
                  style: TextStyle(fontSize: 13, color: AppTheme.textMuted)),
              const SizedBox(height: 20),
              ElevatedButton(onPressed: _load, child: const Text('Coba Lagi')),
            ],
          ),
        ),
      );
    }
    final filtered = _filtered;
    if (filtered.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.surface,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(Icons.search_off_rounded,
                  size: 48, color: AppTheme.textMuted),
            ),
            const SizedBox(height: 16),
            const Text('Gedung tidak ditemukan',
                style: TextStyle(fontSize: 16, color: AppTheme.textSecondary)),
          ],
        ),
      );
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
        itemCount: filtered.length,
        itemBuilder: (_, i) => _GedungCard(
          g: filtered[i],
          index: i,
          onTap: () => Navigator.push(context,
              _slideRight(GedungDetailScreen(gedung: filtered[i]))),
        ),
      ),
    );
  }

  Widget _buildShimmer() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade200,
      highlightColor: Colors.grey.shade100,
      child: ListView.builder(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
        itemCount: 4,
        itemBuilder: (_, i) => Container(
          margin: const EdgeInsets.only(bottom: 16),
          height: 280,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                height: 160,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(height: 16, width: 200, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(4))),
                    const SizedBox(height: 10),
                    Container(height: 12, width: 140, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(4))),
                    const SizedBox(height: 10),
                    Container(height: 14, width: 100, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(4))),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Route _slideRight(Widget page) => PageRouteBuilder(
        pageBuilder: (_, __, ___) => page,
        transitionsBuilder: (_, a, __, child) =>
            SlideTransition(position: Tween(begin: const Offset(0.25, 0), end: Offset.zero).animate(a), child: child),
        transitionDuration: const Duration(milliseconds: 300),
      );

  Route _fadeReplace(Widget page) => PageRouteBuilder(
        pageBuilder: (_, __, ___) => page,
        transitionsBuilder: (_, a, __, child) =>
            FadeTransition(opacity: a, child: child),
        transitionDuration: const Duration(milliseconds: 300),
      );
}

class _GedungCard extends StatelessWidget {
  final Gedung g;
  final int index;
  final VoidCallback onTap;
  const _GedungCard({required this.g, required this.index, required this.onTap});

  String _fmt(int n) => n.toString().replaceAllMapped(
      RegExp(r'(\d)(?=(\d{3})+(?!\d))'), (m) => '${m[1]}.');

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        decoration: AppTheme.premiumCard,
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                Container(
                  height: 160,
                  width: double.infinity,
                  color: AppTheme.surface,
                  child: g.gambar != null && g.gambar!.startsWith('http')
                      ? NetworkImageLoader(
                          url: g.gambar!,
                          fit: BoxFit.cover,
                          width: double.infinity,
                          height: 160,
                        )
                      : _imgPlaceholder(),
                ),
                Positioned(
                  right: 12,
                  bottom: 12,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withValues(alpha: 0.85),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppTheme.gold.withValues(alpha: 0.3)),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.people_outline,
                            size: 12, color: AppTheme.goldLight),
                        const SizedBox(width: 4),
                        Text('${g.kapasitas} orang',
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w500)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(g.nama,
                      style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w700,
                          color: AppTheme.textPrimary,
                          letterSpacing: -0.3)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Icon(Icons.location_on_outlined,
                          size: 14, color: AppTheme.textMuted),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(g.alamat,
                            style: const TextStyle(
                                fontSize: 12,
                                color: AppTheme.textSecondary),
                            overflow: TextOverflow.ellipsis),
                      ),
                    ],
                  ),
                  const Divider(height: 18),
                  Row(
                    children: [
                      const Spacer(),
                      Text('/hari',
                          style: TextStyle(
                              fontSize: 11,
                              color: AppTheme.textMuted)),
                      const SizedBox(width: 2),
                      Text('Rp ${_fmt(g.hargaSewa.toInt())}',
                          style: AppTheme.priceStyle),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    ).animate().fadeIn(
        delay: (50 * index).ms,
        duration: 400.ms,
        curve: Curves.easeOut).slideY(begin: 0.05, end: 0, duration: 400.ms);
  }

  Widget _imgPlaceholder() {
    return Container(
      height: 160,
      color: AppTheme.surface,
      child: Center(
        child: Icon(Icons.image_outlined, size: 48, color: AppTheme.textMuted.withValues(alpha: 0.5)),
      ),
    );
  }
}
