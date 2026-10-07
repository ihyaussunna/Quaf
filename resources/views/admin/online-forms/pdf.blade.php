<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isJudgeView ? 'Jury Evaluation Dossier' : 'Candidate Submissions' }} — {{ $program->name ?? $form?->title }} ({{ $program->code ?? 'PRG' }})</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm 14mm 14mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            line-height: 1.5;
            padding: 20px;
        }

        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 24px auto;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #be1e2d;
            color: #ffffff;
        }

        .btn-primary:hover {
            background: #9e1825;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .dossier-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .submission-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 28px;
            page-break-after: always;
            break-after: page;
        }

        .submission-card:last-child {
            page-break-after: auto;
            break-after: auto;
            margin-bottom: 0;
        }

        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 20px;
        }

        .logo-wrap img {
            height: 48px;
            width: auto;
        }

        .header-meta {
            text-align: right;
        }

        .fest-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #be1e2d;
        }

        .fest-sub {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .candidate-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .code-box {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            background: #0f172a;
            color: #facc15;
            font-size: 26px;
            font-weight: 900;
            font-family: monospace;
            border-radius: 12px;
            border: 2px solid #facc15;
        }

        .code-text-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
        }

        .code-text-val {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
        }

        .candidate-meta-right {
            text-align: right;
            font-size: 12px;
            color: #475569;
        }

        .section-heading {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .content-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 18px 20px;
            margin-bottom: 20px;
        }

        .text-content {
            font-family: "Georgia", Cambria, serif;
            font-size: 14px;
            line-height: 1.8;
            color: #1e293b;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .image-preview-box {
            text-align: center;
            margin-bottom: 20px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px;
        }

        .image-preview-box img {
            max-width: 100%;
            max-height: 520px;
            border-radius: 6px;
            object-fit: contain;
            border: 1px solid #e2e8f0;
        }

        .media-link-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            gap: 16px;
        }

        .media-url {
            font-family: monospace;
            font-size: 12px;
            color: #0369a1;
            word-break: break-all;
        }

        .qr-stamp {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
            background: #ffffff;
            padding: 4px;
        }

        .scoring-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1.5px dashed #94a3b8;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .score-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-top: 12px;
        }

        .score-slot {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #f8fafc;
        }

        .score-slot-label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }

        .score-slot-line {
            font-size: 14px;
            font-family: monospace;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 2px;
            min-height: 22px;
        }

        .signature-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 11px;
            color: #475569;
            font-weight: 600;
        }

        .sig-line {
            width: 220px;
            border-bottom: 1px solid #0f172a;
            margin-bottom: 4px;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .submission-card {
                border: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin-bottom: 0 !important;
            }

            .content-box {
                border: 1px solid #000000 !important;
            }

            .image-preview-box {
                border: 1px solid #000000 !important;
            }

            .candidate-banner {
                border: 1px solid #000000 !important;
                background: #f8fafc !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Toolbar (Hidden on Print) -->
    <div class="no-print-bar">
        <div>
            <div style="font-size: 15px; font-weight: 800; color: #0f172a;">
                {{ $program->name ?? $form?->title }}
            </div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Code: <strong>{{ $program->code ?? 'PRG' }}</strong> • 
                Total Submissions: <strong style="color: #be1e2d;">{{ $submissions->count() }}</strong> • 
                {{ $isJudgeView ? 'Confidential Jury Evaluation Mode' : 'Evaluation Dossier' }}
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            @if(!$isJudgeView)
                <button type="button" onclick="toggleStudentDetails()" id="toggleBtn" class="btn btn-secondary">
                    Toggle Student Names
                </button>
            @endif

            <button type="button" onclick="window.print()" class="btn btn-primary">
                Print / Save as PDF
            </button>

            <button type="button" onclick="window.close()" class="btn btn-secondary">
                Close
            </button>
        </div>
    </div>

    <!-- Submissions Dossier -->
    <div class="dossier-wrapper">
        @forelse($submissions as $index => $sub)
            @php
                $codeLetter = $sub->code_letter ?: chr(65 + $index);
                $isImage = $sub->file_path && in_array(strtolower($sub->file_type ?? pathinfo($sub->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg']);
                $wordCount = $sub->text_content ? str_word_count(strip_tags($sub->text_content)) : 0;
            @endphp

            <div class="submission-card">
                <!-- Header -->
                <div class="doc-header">
                    <div class="logo-wrap">
                        <img src="{{ asset('images/forms-header-logo.svg') }}" alt="QUAF Logo">
                    </div>
                    <div class="header-meta">
                        <div class="fest-title">QUAF 9.0 • Islamic Educational Board of India</div>
                        <div class="fest-sub">Official Festival Evaluation Record • Confidential Evaluation</div>
                    </div>
                </div>

                <!-- Candidate Code Banner -->
                <div class="candidate-banner">
                    <div class="code-box">
                        <div class="code-badge">
                            {{ $codeLetter }}
                        </div>
                        <div>
                            <div class="code-text-label">Candidate Code Letter</div>
                            <div class="code-text-val">Code {{ $codeLetter }}</div>
                        </div>
                    </div>

                    <div class="candidate-meta-right">
                        <div><strong>Program:</strong> {{ $program->name ?? $form?->title }} ({{ $program->code ?? 'PRG' }})</div>
                        <div><strong>Category / Zone:</strong> {{ $program->category->name ?? 'General' }} • {{ $program->zone->name ?? $program->eligibility ?? 'All Zones' }}</div>
                        <div><strong>Submitted At:</strong> {{ $sub->submitted_at?->format('d M Y, h:i A') ?? $sub->created_at->format('d M Y, h:i A') }}</div>

                        @if(!$isJudgeView)
                            <div class="student-detail-row" style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #cbd5e1; font-size: 11px; color: #64748b;">
                                <strong>Candidate:</strong> {{ $sub->student_name ?: 'Participant' }} 
                                @if($sub->chest_number) (Chest #{{ $sub->chest_number }}) @endif
                                • {{ $sub->group?->name ?? 'Group' }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 1. Text Content -->
                @if($sub->text_content)
                    <div>
                        <div class="section-heading">
                            <span>Written Submission</span>
                            <span style="font-size: 11px; color: #64748b; font-weight: 600;">({{ $wordCount }} words • {{ strlen($sub->text_content) }} characters)</span>
                        </div>
                        <div class="content-box">
                            <div class="text-content">{{ $sub->text_content }}</div>
                        </div>
                    </div>
                @endif

                <!-- 2. Image / Artwork / Drawing -->
                @if($sub->file_path && $isImage)
                    <div>
                        <div class="section-heading">
                            <span>Uploaded Artwork / Document Image</span>
                            <span style="font-size: 11px; color: #64748b; font-weight: 600;">({{ $sub->file_name ?? 'Image' }} • {{ $sub->formatted_file_size }})</span>
                        </div>
                        <div class="image-preview-box">
                            <img src="{{ $sub->file_url }}" alt="Artwork submission for Code {{ $codeLetter }}">
                        </div>
                    </div>
                @elseif($sub->file_path)
                    <!-- Non-image file (PDF / DOC) -->
                    <div>
                        <div class="section-heading">
                            <span>Uploaded Document</span>
                        </div>
                        <div class="media-link-card">
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">
                                    {{ $sub->file_name ?? 'Document File' }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    Format: {{ strtoupper($sub->file_type ?? 'PDF') }} • Size: {{ $sub->formatted_file_size }}
                                </div>
                                <div class="media-url" style="margin-top: 4px;">
                                    {{ $sub->file_url }}
                                </div>
                            </div>
                            @if($sub->file_url)
                                <img src="{{ \App\Services\QrCodeService::url($sub->file_url, 100) }}" alt="Document QR" class="qr-stamp">
                            @endif
                        </div>
                    </div>
                @endif

                <!-- 3. Video Submission -->
                @if($sub->video_url)
                    <div>
                        <div class="section-heading">
                            <span>Video Submission Link</span>
                        </div>
                        <div class="media-link-card">
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">
                                    Video Performance Link
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                    Scan QR code with phone camera to watch directly
                                </div>
                                <div class="media-url" style="margin-top: 4px;">
                                    {{ $sub->video_url }}
                                </div>
                            </div>
                            <img src="{{ \App\Services\QrCodeService::url($sub->video_url, 100) }}" alt="Video QR" class="qr-stamp">
                        </div>
                    </div>
                @endif

                <!-- Jury Evaluation & Marking Sheet (Printable Footer) -->
                <div class="scoring-footer">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px;">
                        Jury Evaluation Scorecard (Code {{ $codeLetter }})
                    </div>

                    <div class="score-grid">
                        <div class="score-slot">
                            <span class="score-slot-label">Presentation / Technique</span>
                            <div class="score-slot-line"></div>
                        </div>
                        <div class="score-slot">
                            <span class="score-slot-label">Content / Adherence</span>
                            <div class="score-slot-line"></div>
                        </div>
                        <div class="score-slot">
                            <span class="score-slot-label">Creativity / Impact</span>
                            <div class="score-slot-line"></div>
                        </div>
                        <div class="score-slot" style="background: #f1f5f9; border-color: #94a3b8;">
                            <span class="score-slot-label" style="color: #be1e2d; font-weight: 800;">Total Score (/ 100)</span>
                            <div class="score-slot-line" style="border-bottom: 2px solid #be1e2d;"></div>
                        </div>
                    </div>

                    <div class="signature-row">
                        <div>
                            <div class="sig-line"></div>
                            <div>Judge Name & Designation</div>
                        </div>
                        <div style="text-align: center;">
                            <div>Date: {{ now()->format('d/m/Y') }}</div>
                        </div>
                        <div style="text-align: right;">
                            <div class="sig-line" style="margin-left: auto;"></div>
                            <div>Official Signature</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 60px 20px; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0;">
                <p style="font-size: 16px; font-weight: 700; color: #475569;">No online submissions recorded for this program yet.</p>
                <p style="font-size: 12px; color: #94a3b8; margin-top: 6px;">Submissions made via student portal will appear here.</p>
            </div>
        @endforelse
    </div>

    <script>
        function toggleStudentDetails() {
            const rows = document.querySelectorAll('.student-detail-row');
            rows.forEach(r => {
                r.style.display = (r.style.display === 'none') ? 'block' : 'none';
            });
            const btn = document.getElementById('toggleBtn');
            if (btn) {
                btn.innerText = (btn.innerText.includes('Hide')) ? 'Show Student Names' : 'Hide Student Names (Blind Jury)';
            }
        }
    </script>
</body>
</html>
