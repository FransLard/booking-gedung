import 'package:flutter/material.dart';

class AppTheme {
  static const Color primary = Color(0xFF0A1628);
  static const Color primaryLight = Color(0xFF162447);
  static const Color primaryMid = Color(0xFF1F3A5F);
  static const Color gold = Color(0xFFD4AF37);
  static const Color goldLight = Color(0xFFF0E0A0);
  static const Color surface = Color(0xFFF8F6F3);
  static const Color cardSurface = Colors.white;
  static const Color textPrimary = Color(0xFF1A1A2E);
  static const Color textSecondary = Color(0xFF8E8E93);
  static const Color textMuted = Color(0xFFB0B0B5);
  static const Color borderLight = Color(0xFFE8E6E1);
  static const Color success = Color(0xFF2E7D32);
  static const Color warning = Color(0xFFE65100);
  static const Color error = Color(0xFFC62828);

  static ThemeData get light => ThemeData(
        useMaterial3: true,
        brightness: Brightness.light,
        colorScheme: ColorScheme.light(
          primary: primary,
          secondary: gold,
          surface: surface,
          error: error,
        ),
        scaffoldBackgroundColor: surface,
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.transparent,
          foregroundColor: primary,
          elevation: 0,
          centerTitle: true,
          scrolledUnderElevation: 0,
          titleTextStyle: TextStyle(
            color: primary,
            fontSize: 18,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.3,
          ),
        ),
        cardTheme: CardThemeData(
          color: cardSurface,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: borderLight, width: 1),
          ),
          clipBehavior: Clip.antiAlias,
          margin: const EdgeInsets.only(bottom: 12),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            backgroundColor: primary,
            foregroundColor: Colors.white,
            elevation: 0,
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
            textStyle: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w600,
              letterSpacing: 0.3,
            ),
          ),
        ),
        outlinedButtonTheme: OutlinedButtonThemeData(
          style: OutlinedButton.styleFrom(
            foregroundColor: primary,
            side: const BorderSide(color: gold, width: 1.5),
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(14),
            ),
            textStyle: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: surface,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: borderLight),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: borderLight),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: gold, width: 1.5),
          ),
          errorBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: error),
          ),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          labelStyle: TextStyle(color: textSecondary, fontSize: 14),
          prefixIconColor: textMuted,
          suffixIconColor: textMuted,
        ),
        dividerTheme: DividerThemeData(
          color: borderLight,
          thickness: 1,
          space: 1,
        ),
        bottomNavigationBarTheme: BottomNavigationBarThemeData(
          backgroundColor: cardSurface,
          elevation: 0,
          selectedItemColor: primary,
          unselectedItemColor: textMuted,
          type: BottomNavigationBarType.fixed,
          selectedLabelStyle: const TextStyle(
              fontWeight: FontWeight.w600, fontSize: 11),
          unselectedLabelStyle:
              const TextStyle(fontWeight: FontWeight.w500, fontSize: 11),
        ),
        chipTheme: ChipThemeData(
          backgroundColor: surface,
          selectedColor: primary,
          labelStyle: const TextStyle(fontSize: 12),
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
        ),
      );

  static List<BoxShadow> get cardShadow => [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.04),
          blurRadius: 16,
          offset: const Offset(0, 4),
        ),
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.02),
          blurRadius: 4,
          offset: const Offset(0, 1),
        ),
      ];

  static final BoxDecoration premiumCard = BoxDecoration(
    color: cardSurface,
    borderRadius: BorderRadius.circular(16),
    border: Border.all(color: borderLight, width: 1),
    boxShadow: cardShadow,
  );

  static final BoxDecoration goldGradient = BoxDecoration(
    gradient: LinearGradient(
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
      colors: [
        gold,
        goldLight,
      ],
    ),
  );

  static final BoxDecoration darkGradient = BoxDecoration(
    gradient: LinearGradient(
      begin: Alignment.topCenter,
      end: Alignment.bottomCenter,
      colors: [
        primary,
        primaryLight,
      ],
    ),
  );

  static final BoxDecoration premiumHeader = BoxDecoration(
    gradient: LinearGradient(
      begin: Alignment.topLeft,
      end: Alignment.bottomRight,
      colors: [
        primary,
        primaryMid,
      ],
    ),
  );

  static const EdgeInsets screenPadding = EdgeInsets.all(20);
  static const EdgeInsets cardPadding = EdgeInsets.all(16);

  static const TextStyle sectionTitle = TextStyle(
    fontSize: 17,
    fontWeight: FontWeight.w700,
    color: textPrimary,
    letterSpacing: -0.3,
  );

  static const TextStyle priceStyle = TextStyle(
    fontSize: 18,
    fontWeight: FontWeight.w700,
    color: gold,
    letterSpacing: 0.5,
  );

  static const TextStyle labelStyle = TextStyle(
    fontSize: 12,
    fontWeight: FontWeight.w500,
    color: textSecondary,
    letterSpacing: 0.3,
  );

  static BoxDecoration statusBadge(String status) {
    Color bg;
    Color fg;
    switch (status) {
      case 'confirmed':
        bg = const Color(0xFFE8F5E9);
        fg = success;
        break;
      case 'cancelled':
        bg = const Color(0xFFFFEBEE);
        fg = error;
        break;
      default:
        bg = const Color(0xFFFFF8E1);
        fg = warning;
    }
    return BoxDecoration(
      color: bg,
      borderRadius: BorderRadius.circular(8),
      border: Border.all(color: fg.withValues(alpha: 0.3)),
    );
  }

  static TextStyle statusText(String status) {
    switch (status) {
      case 'confirmed':
        return const TextStyle(color: success, fontSize: 11, fontWeight: FontWeight.w600);
      case 'cancelled':
        return const TextStyle(color: error, fontSize: 11, fontWeight: FontWeight.w600);
      default:
        return const TextStyle(color: warning, fontSize: 11, fontWeight: FontWeight.w600);
    }
  }
}
