import 'package:flutter_test/flutter_test.dart';

import 'package:booking_mobile/main.dart';
import 'package:booking_mobile/screens/welcome_screen.dart';

void main() {
  testWidgets('App loads WelcomeScreen', (WidgetTester tester) async {
    await tester.pumpWidget(const BookingApp());
    expect(find.byType(WelcomeScreen), findsOneWidget);
  });
}
