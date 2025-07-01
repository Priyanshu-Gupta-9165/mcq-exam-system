# 🎓 MCQ Exam System

A comprehensive Multiple Choice Question (MCQ) examination system built with PHP and MySQL. This system allows administrators to create and manage exams while students can take exams and view their results.

> **Copyright © 2023 Priyanshu Gupta. All rights reserved.** See [COPYRIGHT](COPYRIGHT) file for details.

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)

## ✨ Key Features

### 👨‍🏫 For Administrators
- **Dashboard Overview**: Complete view of system statistics and activities
- **Exam Management**: Create, edit, and delete exams with ease
- **Question Bank**: Add multiple-choice questions with options
- **Result Analysis**: View detailed performance reports
- **User Management**: Monitor and manage student accounts

### 👨‍🎓 For Students
- **User-Friendly Interface**: Easy-to-navigate dashboard
- **Exam Taking**: Smooth and intuitive exam interface
- **Instant Results**: Get scores immediately after completion
- **Progress Tracking**: View performance history and improvements
- **Secure Environment**: Safe and monitored exam experience

### 🛠️ Technical Features
- **Responsive Design**: Works on all devices
- **Modern UI**: Bootstrap 5 based interface
- **Secure Authentication**: Protected login system
- **Database Management**: Efficient MySQL integration
- **Activity Logging**: Comprehensive action tracking

## 🛠️ System Requirements

- ⚡ PHP 7.4 or higher
- 🗄️ MySQL 5.7 or higher
- 🌐 Apache/Nginx web server
- 🌍 Web browser (Chrome, Firefox, Safari, etc.)
- 🔧 XAMPP (recommended for local development)

## 🚀 Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Priyanshu-Gupta-9165/mcq-exam-system.git
   ```

2. **Database Setup**
   - Create a new MySQL database named `mcq`
   - Import the following SQL files in order:
     1. `users.sql`
     2. `exams.sql`
     3. `questions.sql`
     4. `options.sql`
     5. `exam_results.sql`
     6. `history.sql`

3. **Configuration**
   - Open `config.php`
   - Update database credentials:
     ```php
     $host = 'localhost';
     $dbname = 'mcq';
     $username = 'your_username';
     $password = 'your_password';
     ```

4. **File Permissions**
   - Ensure the web server has read/write permissions to the project directory
   - Set appropriate permissions for upload directories if any

## 📁 Directory Structure

```
MCQ/
├── admin/
│   ├── create_exam.php
│   ├── manage_exams.php
│   ├── results.php
│   ├── edit_exam.php
│   ├── view_exam.php
│   ├── delete_exam.php
│   ├── add_question.php
│   ├── edit_question.php
│   └── delete_question.php
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── includes/
│   ├── functions.php
│   └── alert.php
├── css/
│   └── style.css
├── config.php
├── index.php
├── dashboard.php
├── take_exam.php
├── history.php
└── README.md
```

## ⚡ Quick Start Guide

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache and MySQL services

2. **Access the System**
   - Open browser and go to: `http://localhost/MCQ`
   - Login with credentials:
     - Admin: username: `admin`, password: (your admin password)
     - Student: Register new account or use existing credentials

## 📊 Database Schema

### 👥 Users Table
- id (Primary Key)
- username
- password
- role (admin/student)
- created_at

### 📝 Exams Table
- id (Primary Key)
- title
- description
- duration
- passing_score
- created_at

### ❓ Questions Table
- id (Primary Key)
- exam_id (Foreign Key)
- question_text
- created_at

### 🔘 Options Table
- id (Primary Key)
- question_id (Foreign Key)
- option_text
- is_correct
- created_at

### 📈 Exam Results Table
- id (Primary Key)
- user_id (Foreign Key)
- exam_id (Foreign Key)
- score
- total_questions
- completed_at

### 📚 History Table
- id (Primary Key)
- user_id (Foreign Key)
- activity_type
- description
- exam_id (Foreign Key)
- created_at

## 🔒 Security Features

- 🔐 Password hashing using PHP's password_hash()
- 🔑 Session management
- 🛡️ SQL injection prevention using prepared statements
- 🛡️ XSS prevention using htmlspecialchars()
- 👮 Role-based access control
- ✅ Input validation and sanitization

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## 👨‍💻 Author

Priyanshu Gupta
- GitHub: [@Priyanshu-Gupta-9165](https://github.com/Priyanshu-Gupta-9165)

## 🙏 Acknowledgments

- 🎨 Bootstrap for the UI framework
- 🎯 Font Awesome for icons
- 🔧 XAMPP for the development environment


## ❓ FAQ

**Q: How do I reset the admin password?**  
A: You can reset it directly in the database using a hashed password.

**Q: Can I customize the exam duration?**  
A: Yes, admins can set custom duration for each exam.

**Q: How are exam scores calculated?**  
A: Scores are calculated as (correct answers / total questions) * 100.

## 📝 Changelog

### Version 1.0.0
- 🎉 Initial release
- 📝 Basic exam management
- ✍️ Student exam taking functionality
- 📊 Results tracking
- 📚 Activity history

## 🗺️ Roadmap

- [ ] 📑 Add question categories
- [ ] ⏱️ Implement exam time tracking
- [ ] 📊 Add result analytics
- [ ] 📄 Export results to PDF
- [ ] 📧 Email notifications
- [ ] 📱 Mobile responsive design improvements

## 📞 Contact

Priyanshu Gupta - [@Priyanshu-Gupta-9165](https://github.com/Priyanshu-Gupta-9165) 
Project Link: [https://github.com/Priyanshu-Gupta-9165/mcq-exam-system](https://github.com/Priyanshu-Gupta-9165/mcq-exam-system)

---

<div align="center">
  <sub>Built with ❤️ by <a href="https://github.com/Priyanshu-Gupta-9165">Priyanshu Gupta</a></sub>
</div> 
