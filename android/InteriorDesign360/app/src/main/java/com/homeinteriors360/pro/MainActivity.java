package com.homeinteriors360.pro;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.graphics.Color;
import android.os.Bundle;
import android.view.Gravity;
import android.view.View;
import android.webkit.CookieManager;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.ScrollView;
import android.widget.TextView;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.OutputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.util.List;
import java.util.Map;

public class MainActivity extends Activity {
    private static final String BASE_URL = "https://homeinteriors360.com";
    private LinearLayout root;
    private TextView message;
    private ProgressBar progress;
    private int otpRequestId = 0;
    private String registeredEmail = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        CookieManager.getInstance().setAcceptCookie(true);
        showSignupScreen();
    }

    private void showSignupScreen() {
        root = screenRoot();
        ImageView logo = logoView();
        TextView title = title("Create professional account");
        TextView subtitle = body("Register with your name, email and mobile number. We will email a one-time password from noreply@homeinteriors360.com.");
        EditText name = input("Full name", false);
        EditText email = input("Email address", false);
        EditText mobile = input("Mobile number", true);
        Button submit = primaryButton("Send OTP");
        message = statusText();
        progress = progressBar();

        submit.setOnClickListener(v -> {
            setBusy(true, "Sending OTP...");
            try {
                JSONObject body = new JSONObject();
                body.put("name", name.getText().toString().trim());
                body.put("email", email.getText().toString().trim());
                body.put("phone", mobile.getText().toString().trim());
                postJson("/api/mobile/professional/register", body, result -> runOnUiThread(() -> {
                    setBusy(false, "");
                    if (result.error != null) {
                        message.setText(result.error);
                        return;
                    }
                    try {
                        JSONObject json = new JSONObject(result.body);
                        otpRequestId = json.optInt("otp_request_id", 0);
                        registeredEmail = email.getText().toString().trim();
                        showOtpScreen();
                    } catch (Exception e) {
                        message.setText("Could not read server response.");
                    }
                }));
            } catch (Exception e) {
                setBusy(false, e.getMessage());
            }
        });

        root.addView(logo);
        root.addView(title);
        root.addView(subtitle);
        root.addView(name);
        root.addView(email);
        root.addView(mobile);
        root.addView(submit);
        root.addView(progress);
        root.addView(message);
        setContentView(wrap(root));
    }

    private void showOtpScreen() {
        root = screenRoot();
        ImageView logo = logoView();
        TextView title = title("Verify email OTP");
        TextView subtitle = body("Enter the 6 digit OTP sent to " + registeredEmail + ". Your profile stays hidden from public listings until onboarding is completed.");
        EditText otp = input("6 digit OTP", true);
        Button verify = primaryButton("Verify and continue");
        Button edit = secondaryButton("Edit details");
        message = statusText();
        progress = progressBar();

        verify.setOnClickListener(v -> {
            setBusy(true, "Verifying...");
            try {
                JSONObject body = new JSONObject();
                body.put("otp_request_id", otpRequestId);
                body.put("email", registeredEmail);
                body.put("otp", otp.getText().toString().trim());
                postJson("/api/mobile/professional/verify-otp", body, result -> runOnUiThread(() -> {
                    setBusy(false, "");
                    if (result.error != null) {
                        message.setText(result.error);
                        return;
                    }
                    showPwa(BASE_URL + "/");
                }));
            } catch (Exception e) {
                setBusy(false, e.getMessage());
            }
        });
        edit.setOnClickListener(v -> showSignupScreen());

        root.addView(logo);
        root.addView(title);
        root.addView(subtitle);
        root.addView(otp);
        root.addView(verify);
        root.addView(edit);
        root.addView(progress);
        root.addView(message);
        setContentView(wrap(root));
    }

    @SuppressLint("SetJavaScriptEnabled")
    private void showPwa(String url) {
        WebView webView = new WebView(this);
        WebSettings settings = webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setLoadWithOverviewMode(true);
        settings.setUseWideViewPort(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);
        webView.setWebViewClient(new WebViewClient());
        webView.loadUrl(url);
        setContentView(webView);
    }

    private void postJson(String path, JSONObject json, ApiCallback callback) {
        new Thread(() -> {
            try {
                URL url = new URL(BASE_URL + path);
                HttpURLConnection connection = (HttpURLConnection) url.openConnection();
                connection.setRequestMethod("POST");
                connection.setRequestProperty("Content-Type", "application/json; charset=UTF-8");
                connection.setRequestProperty("Accept", "application/json");
                connection.setDoOutput(true);
                byte[] bytes = json.toString().getBytes(StandardCharsets.UTF_8);
                try (OutputStream stream = connection.getOutputStream()) {
                    stream.write(bytes);
                }

                int status = connection.getResponseCode();
                Map<String, List<String>> headers = connection.getHeaderFields();
                List<String> cookies = headers.get("Set-Cookie");
                if (cookies != null) {
                    CookieManager manager = CookieManager.getInstance();
                    for (String cookie : cookies) {
                        manager.setCookie(BASE_URL, cookie);
                    }
                    manager.flush();
                }

                BufferedReader reader = new BufferedReader(new InputStreamReader(
                    status >= 200 && status < 300 ? connection.getInputStream() : connection.getErrorStream(),
                    StandardCharsets.UTF_8
                ));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                String body = response.toString();
                if (status < 200 || status >= 300) {
                    String error = "Request failed.";
                    try {
                        error = new JSONObject(body).optString("error", error);
                    } catch (Exception ignored) {
                    }
                    callback.done(new ApiResult(null, error));
                    return;
                }
                callback.done(new ApiResult(body, null));
            } catch (Exception e) {
                callback.done(new ApiResult(null, e.getMessage()));
            }
        }).start();
    }

    private ScrollView wrap(View view) {
        ScrollView scroll = new ScrollView(this);
        scroll.setBackgroundColor(Color.rgb(248, 250, 252));
        scroll.addView(view);
        return scroll;
    }

    private LinearLayout screenRoot() {
        LinearLayout layout = new LinearLayout(this);
        layout.setOrientation(LinearLayout.VERTICAL);
        layout.setGravity(Gravity.CENTER_HORIZONTAL);
        int pad = dp(22);
        layout.setPadding(pad, dp(48), pad, pad);
        return layout;
    }

    private ImageView logoView() {
        ImageView logo = new ImageView(this);
        logo.setImageResource(R.drawable.logo);
        logo.setAdjustViewBounds(true);
        logo.setContentDescription("HomeInteriors360");
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(dp(190), dp(86));
        params.setMargins(0, 0, 0, dp(18));
        logo.setLayoutParams(params);
        return logo;
    }

    private TextView title(String text) {
        TextView view = new TextView(this);
        view.setText(text);
        view.setTextColor(Color.rgb(17, 24, 39));
        view.setTextSize(28);
        view.setGravity(Gravity.CENTER);
        view.setTypeface(null, 1);
        view.setPadding(0, 0, 0, dp(12));
        return view;
    }

    private TextView body(String text) {
        TextView view = new TextView(this);
        view.setText(text);
        view.setTextColor(Color.rgb(71, 85, 105));
        view.setTextSize(15);
        view.setGravity(Gravity.CENTER);
        view.setPadding(0, 0, 0, dp(22));
        return view;
    }

    private EditText input(String hint, boolean numeric) {
        EditText input = new EditText(this);
        input.setHint(hint);
        input.setSingleLine(true);
        input.setTextSize(16);
        input.setPadding(dp(14), dp(12), dp(14), dp(12));
        if (numeric) {
            input.setInputType(android.text.InputType.TYPE_CLASS_PHONE);
        }
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(-1, -2);
        params.setMargins(0, 0, 0, dp(12));
        input.setLayoutParams(params);
        return input;
    }

    private Button primaryButton(String text) {
        Button button = new Button(this);
        button.setText(text);
        button.setTextColor(Color.WHITE);
        button.setBackgroundColor(Color.rgb(17, 24, 39));
        button.setAllCaps(false);
        button.setTextSize(16);
        button.setPadding(dp(12), dp(12), dp(12), dp(12));
        button.setLayoutParams(buttonParams());
        return button;
    }

    private Button secondaryButton(String text) {
        Button button = primaryButton(text);
        button.setTextColor(Color.rgb(17, 24, 39));
        button.setBackgroundColor(Color.TRANSPARENT);
        return button;
    }

    private LinearLayout.LayoutParams buttonParams() {
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(-1, -2);
        params.setMargins(0, dp(4), 0, dp(10));
        return params;
    }

    private ProgressBar progressBar() {
        ProgressBar bar = new ProgressBar(this);
        bar.setVisibility(View.GONE);
        return bar;
    }

    private TextView statusText() {
        TextView view = new TextView(this);
        view.setTextColor(Color.rgb(185, 28, 28));
        view.setTextSize(14);
        view.setGravity(Gravity.CENTER);
        view.setPadding(0, dp(10), 0, 0);
        return view;
    }

    private void setBusy(boolean busy, String text) {
        if (progress != null) {
            progress.setVisibility(busy ? View.VISIBLE : View.GONE);
        }
        if (message != null) {
            message.setText(text);
        }
    }

    private int dp(int value) {
        return (int) (value * getResources().getDisplayMetrics().density);
    }

    interface ApiCallback {
        void done(ApiResult result);
    }

    static class ApiResult {
        final String body;
        final String error;

        ApiResult(String body, String error) {
            this.body = body;
            this.error = error;
        }
    }
}
