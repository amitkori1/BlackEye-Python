import unittest
from unittest.mock import patch, MagicMock
import sys
import io

# Import the main function from main.py
from main import main

class TestMain(unittest.TestCase):

    @patch('builtins.input')
    @patch('subprocess.Popen')
    @patch('sys.stdout', new_callable=io.StringIO)
    def test_main_happy_path(self, mock_stdout, mock_popen, mock_input):
        # Setup mocks
        mock_input.side_effect = ['1', 'mysubdomain']

        mock_process = MagicMock()
        mock_process.communicate.return_value = (b'mocked output', b'')
        mock_popen.return_value = mock_process

        # Run main
        main()

        # Assertions
        mock_popen.assert_called_once_with(
            "sudo php -t sites/instagram -S 127.0.0.1:80 & ssh -R mysubdomain.serveo.net:80:127.0.0.1:80 serveo.net",
            shell=True
        )
        mock_process.communicate.assert_called_once()
        self.assertIn("Starting Server at mysubdomain.serveo.net...", mock_stdout.getvalue())

    @patch('builtins.input')
    @patch('sys.stdout', new_callable=io.StringIO)
    def test_main_ebay_exit(self, mock_stdout, mock_input):
        # Setup mock for Ebay choice
        mock_input.side_effect = ['18']

        # Expect SystemExit when 18 is chosen
        with self.assertRaises(SystemExit) as cm:
            main()

        self.assertEqual(cm.exception.code, 0)
        self.assertIn("Ebay Currently Does Not Work. Choose Another..", mock_stdout.getvalue())

    @patch('builtins.input')
    @patch('sys.stdout', new_callable=io.StringIO)
    def test_main_keyboard_interrupt(self, mock_stdout, mock_input):
        # Setup mock to raise KeyboardInterrupt
        mock_input.side_effect = KeyboardInterrupt()

        try:
            main()
        except KeyboardInterrupt:
            self.fail("main() raised KeyboardInterrupt unexpectedly!")

if __name__ == '__main__':
    unittest.main()
