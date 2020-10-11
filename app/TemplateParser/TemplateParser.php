<?php

namespace App\TemplateParser;

use App\Repositories\Contact\Contact;
use App\Repositories\Template\Template;

class TemplateParser
{
    public function preview(Contact $contact, array $templates)
    {
        $preview = null;

        foreach ($templates as $template) {
            $p = $this->parse($template, $contact->data);

            if ($preview === null || $preview['weight'] <= $p['weight']) {
                $preview = $p;
            }
        }

        return $preview['content'];
    }

    public function parse(Template $template, array $data)
    {
        $data = array_filter($data);
        $replacement = array_values($data);
        $patterns = array_keys($data);
        $patterns = array_map(function ($key) {
            return "#{{\s?$key\s?}}#";
        }, $patterns);

        $message = preg_replace("#\n#", "<br/>", $template->message);

        $subject = [$template->subject, $message];
        $variables = preg_match_all("#{{\s?(\S+)\s?}}#", implode("\n\n", $subject), $matches);
        $parsed = preg_replace($patterns, $replacement, $subject, -1, $count);
        $parsed = array_combine(['subject', 'message'], $parsed);
        $weight = $this->weightAnalyzer($count, $variables);

        return [
            'content' => $parsed,
            'weight' => $weight,
        ];
    }

    protected function weightAnalyzer($replacements, $slots)
    {
        if ($slots > 0) {
            $weight = (($replacements / $slots) * 100) + $slots;
            $weight = number_format($weight, 2);
        }

        if ($slots == 0) {
            $weight = 100;
        }

        return $weight;
    }
}
