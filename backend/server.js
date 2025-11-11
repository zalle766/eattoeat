const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const multer = require('multer');
const path = require('path');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 5000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use('/uploads', express.static('uploads'));

// MySQL Database Connection
const db = mysql.createConnection({
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'eat_to_eat',
  charset: 'utf8mb4'
});

db.connect((err) => {
  if (err) {
    console.error('Database connection failed:', err);
    process.exit(1);
  }
  console.log('Connected to MySQL database');
});

// JWT Secret
const JWT_SECRET = process.env.JWT_SECRET || 'your-secret-key-change-in-production';

// Multer configuration for file uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + '-' + file.originalname);
  }
});

const upload = multer({ storage: storage });

// Authentication middleware
const authenticateToken = (req, res, next) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];

  if (!token) {
    return res.status(401).json({ success: false, message: 'Access token required' });
  }

  jwt.verify(token, JWT_SECRET, (err, user) => {
    if (err) {
      return res.status(403).json({ success: false, message: 'Invalid token' });
    }
    req.user = user;
    next();
  });
};

// Routes

// User Registration
app.post('/api/auth/register', async (req, res) => {
  try {
    const {
      firstName,
      lastName,
      email,
      phoneNumber,
      password,
      city,
      neighborhood,
      street,
      latitude,
      longitude,
      fullAddress
    } = req.body;

    // Check if user already exists
    const existingUser = await db.promise().query(
      'SELECT id FROM users WHERE email = ?',
      [email]
    );

    if (existingUser[0].length > 0) {
      return res.status(400).json({
        success: false,
        message: 'User already exists with this email'
      });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 10);

    // Insert user
    const result = await db.promise().query(
      `INSERT INTO users (first_name, last_name, email, phone_number, password_hash, 
       city, neighborhood, street, latitude, longitude, full_address, created_at) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
      [firstName, lastName, email, phoneNumber, hashedPassword, city, neighborhood, 
       street, latitude, longitude, fullAddress]
    );

    const userId = result[0].insertId;

    // Generate JWT token
    const token = jwt.sign(
      { userId: userId, email: email, role: 'customer' },
      JWT_SECRET,
      { expiresIn: '7d' }
    );

    // Get user data
    const userData = await db.promise().query(
      'SELECT id, first_name, last_name, email, phone_number, city, neighborhood, created_at FROM users WHERE id = ?',
      [userId]
    );

    res.status(201).json({
      success: true,
      message: 'User registered successfully',
      data: {
        user: userData[0][0],
        token: token
      }
    });

  } catch (error) {
    console.error('Registration error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// User Login
app.post('/api/auth/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    // Find user
    const user = await db.promise().query(
      'SELECT * FROM users WHERE email = ?',
      [email]
    );

    if (user[0].length === 0) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }

    const userData = user[0][0];

    // Check password
    const isValidPassword = await bcrypt.compare(password, userData.password_hash);
    if (!isValidPassword) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password'
      });
    }

    // Generate JWT token
    const token = jwt.sign(
      { userId: userData.id, email: userData.email, role: 'customer' },
      JWT_SECRET,
      { expiresIn: '7d' }
    );

    // Remove password from response
    delete userData.password_hash;

    res.json({
      success: true,
      message: 'Login successful',
      data: {
        user: userData,
        token: token
      }
    });

  } catch (error) {
    console.error('Login error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Get user profile
app.get('/api/user/profile', authenticateToken, async (req, res) => {
  try {
    const user = await db.promise().query(
      'SELECT id, first_name, last_name, email, phone_number, city, neighborhood, street, latitude, longitude, full_address, created_at FROM users WHERE id = ?',
      [req.user.userId]
    );

    if (user[0].length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }

    res.json({
      success: true,
      data: user[0][0]
    });

  } catch (error) {
    console.error('Get profile error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Update user profile
app.put('/api/user/profile', authenticateToken, async (req, res) => {
  try {
    const {
      firstName,
      lastName,
      phoneNumber,
      city,
      neighborhood,
      street,
      latitude,
      longitude,
      fullAddress
    } = req.body;

    await db.promise().query(
      `UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, 
       city = ?, neighborhood = ?, street = ?, latitude = ?, longitude = ?, 
       full_address = ?, updated_at = NOW() WHERE id = ?`,
      [firstName, lastName, phoneNumber, city, neighborhood, street, 
       latitude, longitude, fullAddress, req.user.userId]
    );

    res.json({
      success: true,
      message: 'Profile updated successfully'
    });

  } catch (error) {
    console.error('Update profile error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Restaurant Registration
app.post('/api/restaurants/register', upload.fields([
  { name: 'coverImage', maxCount: 1 },
  { name: 'menuImages', maxCount: 5 },
  { name: 'licenseImages', maxCount: 3 },
  { name: 'ownerDocuments', maxCount: 2 }
]), async (req, res) => {
  try {
    const restaurantData = JSON.parse(req.body.restaurantData);
    const files = req.files;

    // Insert restaurant
    const result = await db.promise().query(
      `INSERT INTO restaurants (name, description, cuisine_type, phone_number, email,
       street, neighborhood, city, latitude, longitude, full_address,
       owner_name, owner_phone, owner_email, owner_id_number,
       partnership_type, partner_name, partner_phone, partner_email,
       license_number, license_type, license_expiry, tax_id, commercial_register,
       operating_hours, cover_image, status, created_at) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
      [
        restaurantData.name, restaurantData.description, restaurantData.cuisineType,
        restaurantData.phoneNumber, restaurantData.email, restaurantData.street,
        restaurantData.neighborhood, restaurantData.city, restaurantData.latitude,
        restaurantData.longitude, restaurantData.fullAddress, restaurantData.ownerName,
        restaurantData.ownerPhone, restaurantData.ownerEmail, restaurantData.ownerIdNumber,
        restaurantData.partnershipType, restaurantData.partnerName, restaurantData.partnerPhone,
        restaurantData.partnerEmail, restaurantData.licenseNumber, restaurantData.licenseType,
        restaurantData.licenseExpiry, restaurantData.taxId, restaurantData.commercialRegister,
        JSON.stringify(restaurantData.operatingHours), 
        files.coverImage ? files.coverImage[0].filename : null,
        'pending'
      ]
    );

    const restaurantId = result[0].insertId;

    // Save file paths
    if (files.menuImages) {
      for (const file of files.menuImages) {
        await db.promise().query(
          'INSERT INTO restaurant_images (restaurant_id, image_path, image_type) VALUES (?, ?, ?)',
          [restaurantId, file.filename, 'menu']
        );
      }
    }

    if (files.licenseImages) {
      for (const file of files.licenseImages) {
        await db.promise().query(
          'INSERT INTO restaurant_images (restaurant_id, image_path, image_type) VALUES (?, ?, ?)',
          [restaurantId, file.filename, 'license']
        );
      }
    }

    res.status(201).json({
      success: true,
      message: 'Restaurant registration submitted successfully',
      data: { restaurantId: restaurantId }
    });

  } catch (error) {
    console.error('Restaurant registration error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Get restaurants
app.get('/api/restaurants', async (req, res) => {
  try {
    const { city, cuisine, status = 'approved' } = req.query;
    
    let query = 'SELECT * FROM restaurants WHERE status = ?';
    let params = [status];

    if (city) {
      query += ' AND city = ?';
      params.push(city);
    }

    if (cuisine) {
      query += ' AND cuisine_type = ?';
      params.push(cuisine);
    }

    query += ' ORDER BY rating DESC, created_at DESC';

    const restaurants = await db.promise().query(query, params);

    res.json({
      success: true,
      data: restaurants[0]
    });

  } catch (error) {
    console.error('Get restaurants error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Get restaurant by ID
app.get('/api/restaurants/:id', async (req, res) => {
  try {
    const { id } = req.params;

    const restaurant = await db.promise().query(
      'SELECT * FROM restaurants WHERE id = ? AND status = "approved"',
      [id]
    );

    if (restaurant[0].length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Restaurant not found'
      });
    }

    // Get restaurant images
    const images = await db.promise().query(
      'SELECT * FROM restaurant_images WHERE restaurant_id = ?',
      [id]
    );

    res.json({
      success: true,
      data: {
        ...restaurant[0][0],
        images: images[0]
      }
    });

  } catch (error) {
    console.error('Get restaurant error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Admin Dashboard - Get pending restaurants
app.get('/api/admin/restaurants/pending', authenticateToken, async (req, res) => {
  try {
    // Check if user is admin
    if (req.user.role !== 'admin') {
      return res.status(403).json({
        success: false,
        message: 'Access denied'
      });
    }

    const restaurants = await db.promise().query(
      'SELECT * FROM restaurants WHERE status = "pending" ORDER BY created_at DESC'
    );

    res.json({
      success: true,
      data: restaurants[0]
    });

  } catch (error) {
    console.error('Get pending restaurants error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Admin Dashboard - Approve/Reject restaurant
app.put('/api/admin/restaurants/:id/status', authenticateToken, async (req, res) => {
  try {
    // Check if user is admin
    if (req.user.role !== 'admin') {
      return res.status(403).json({
        success: false,
        message: 'Access denied'
      });
    }

    const { id } = req.params;
    const { status, reason } = req.body;

    await db.promise().query(
      'UPDATE restaurants SET status = ?, admin_reason = ?, updated_at = NOW() WHERE id = ?',
      [status, reason, id]
    );

    res.json({
      success: true,
      message: `Restaurant ${status} successfully`
    });

  } catch (error) {
    console.error('Update restaurant status error:', error);
    res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
});

// Start server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`API available at http://localhost:${PORT}/api`);
});

module.exports = app;
