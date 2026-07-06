---
title: "Workspaces, members & ownership"
description: "What a workspace is, how to create and switch between them, how to manage members and roles, how to leave a workspace, transfer ownership, delete a workspace, and get one back."
status: published
kind: evergreen-explainer
audience: users
published_at: "2026-07-06"
---

# Workspaces, members & ownership

Everything you do in the app happens inside a **workspace** — a shared home for a
team's releases and the tools connected to them. This guide explains what a
workspace is, how to create one and switch between them, how to manage who's in
yours and their roles, how to invite people (and accept an invite), how to leave
or hand off ownership, and how to delete a workspace — and get one back if you
change your mind.

## What a workspace is

A workspace is the container your team works in. Your releases, the tools you've
connected, and the people who can see them all belong to one workspace. When you
sign in, you're always looking at a single workspace at a time — its name shows in
the top-left of the sidebar, above the main navigation, and again next to your
name at the bottom.

Because the workspace frames everything, the sidebar links — **Releases** and
**Connections** — always show what's inside *this* workspace. Nothing leaks
between workspaces: a release created in one is never visible from another.

Every member of a workspace has one of two roles:

- **Member** — can see and work on the workspace's releases and connections.
- **Owner** — everything a member can do, plus managing the team: inviting
  people, changing roles, removing members, and deleting the workspace. Owners
  also see an extra **Live transport** link in the sidebar for checking broadcast
  health.

## Creating a workspace

You don't have to wait to be invited — you can start your own. Open the workspace
menu (the workspace name at the top of the sidebar) and choose **Create
workspace**, or head to the **Create a workspace** screen directly.

Give it a **Workspace name** — something like *Acme Engineering* — and optionally
a URL slug. Leave the slug blank and the app generates one from the name for you.
Select **Create workspace** and you're done: you become its first **owner** and
land straight in the new, empty workspace, ready to connect a tool or start a
release.

If the URL you picked is already taken, the app tells you so you can choose
another name — it never quietly attaches you to someone else's workspace.

## Switching between workspaces

You can belong to more than one workspace — your own, plus any you've been invited
to. The **workspace switcher** at the top of the sidebar shows the one you're in
now and lets you jump between them.

Open it to see every workspace you belong to, with your current one marked as
active. Select another to **switch** to it — the sidebar, releases, and
connections all reload to show that workspace. The same menu always offers
**Members & invites** for the current workspace and **Create workspace** if you
want to start a fresh one.

## Viewing and managing members

Open the members screen — **Members & invites** in the workspace menu — to see
everyone in the workspace. Each person is listed with their name, email, and a
badge showing whether they're an **owner** or a **member**. The list is open to
everyone in the workspace, so any member can see who else is on the team.

If you're an **owner**, each row also carries the management controls: changing a
person's role and removing them. Members without owner permissions see the roster
but not the controls.

One rule protects every workspace: **it must always keep at least one owner.** The
app won't let you remove — or step down — the last remaining owner, so a workspace
is never left without someone who can manage it.

## Roles and transferring ownership

Roles aren't fixed — an owner can promote a teammate or hand off ownership
entirely.

- **Make someone an owner.** On a member's row, choose the promote action and
  confirm **Make _{name}_ an owner?** They'll then be able to manage members,
  roles, and every project in the workspace.
- **Step an owner down.** Use the same control on an owner's row and confirm
  **Remove _{name}_ as owner?** to move them back to a plain member. You can step
  *yourself* down this way too — as long as another owner remains.

Because a workspace must always keep at least one owner, the app blocks the change
that would leave it with none. To **fully hand over** a workspace you own alone:
first promote another member to owner, then either step yourself down to member or
leave the workspace. That order is enforced — you can't demote or remove the last
owner until a replacement exists.

## Inviting people

Owners invite teammates with a shareable **invite link**. On the members screen,
find the **Invite someone** panel, enter the person's email address, choose the
role they should join as (**Member** or **Owner**), and select **Create invite
link**.

The new invite appears under **Pending invites**. Use **Copy link** to grab the
invite URL and send it to your teammate however you like — email, chat, wherever.
Each invite is tied to the email address you entered, so it can only be accepted by
someone signed in with that same address.

A few things worth knowing about invites:

- **They expire.** An invite link is valid for 14 days. After that it shows as
  **Expired** and can't be used — just create a fresh one.
- **You can revoke them.** Changed your mind, or sent one to the wrong address?
  Select **Revoke** on a pending invite to cancel it.
- **No duplicates.** If the email already belongs to a workspace member, the app
  tells you rather than creating a second invite.

## Accepting an invite

When you open an invite link, you'll see a short page naming the workspace you've
been invited to, who invited you, and the role you'll join as. If you're signed in
with the email the invite was sent to, select **Accept invite** to join. You'll
land right in the workspace's members screen as a new member.

If the invite can't be accepted, the page explains why instead of showing the
button:

- **Expired** — the link is more than 14 days old. Ask the owner for a new one.
- **Already accepted** — this invite has already been used.
- **Already a member** — you're in this workspace already; nothing to do.
- **Wrong email** — you're signed in with a different address than the invite was
  issued for. Sign in with the invited email and open the link again.
- **Not valid** — the link is broken or was revoked.

You need to be signed in to accept an invite. If you don't have an account yet,
create one using the invited email address first, then open the link.

## Removing a member

Owners can take someone out of the workspace from the members screen: on their
row, select **Remove** and confirm. That person immediately loses access — the
workspace, its releases, and its connections disappear for them, and any work they
were holding is released so nothing stays locked behind them.

The last-owner rule still applies: you can't remove the only remaining owner.
Step in a second owner first if you need to.

## Leaving a workspace

Want out of a workspace yourself? On the members screen, select **Leave** and
confirm **Leave workspace**. You're removed from it and dropped back to your next
workspace (or the **No workspace yet** screen if it was your only one). Anything
you were holding in that workspace is released as you go.

Any member can leave at any time — with one exception that protects the workspace:
**the sole owner can't leave.** If you're the only owner, the **Leave** action is
unavailable until you promote another member to owner (see *Roles and transferring
ownership* above). Once someone else can run the workspace, you're free to go.

## Deleting a workspace

When a workspace has run its course, an **owner** can delete it. On the members
screen, scroll to the **Delete workspace** panel, select **Delete workspace**, and
confirm **Delete _{workspace}_?**

Deleting is thorough: the workspace is removed for **everyone**, its projects and
releases are archived, and every member is signed out of it. Any active holds on
its work are released, and pending invites stop working. It's the clean way to
retire a team's space — not something to do lightly.

A shared workspace is protected here too. **If the workspace has other owners
besides you, you can't delete it on your own** — the button is disabled with a note
that *other owners must step down or leave first*. This makes sure one owner can't
pull a shared workspace out from under the rest of the team. Once you're the only
owner left, the delete is yours to make.

## Getting a deleted workspace back

Deleting can't be undone from inside the app — but the workspace isn't gone for
good. A delete is a soft archive: the workspace, its projects, and its releases are
all preserved behind the scenes.

If you deleted a workspace by mistake, **contact support** and we can restore it,
along with the projects and releases it contained. (Recovery is a support-side
operation — there's no self-serve restore button in the app, so reach out and we'll
handle it.) Members will need to be re-added, or will rejoin as their access is
restored.

## When you're not in a workspace yet

If you sign in and you're not a member of any workspace, you'll see a **No
workspace yet** screen instead of the usual navigation. This is normal for a brand
new account that hasn't been invited anywhere.

From here you can **Create a workspace** to start your own, or ask a workspace
owner to invite you (they'll send you an invite link as described above). Once you
create one or accept an invite, your workspace and its releases appear and the full
app opens up. If you're joining by invite, make sure it's sent to the same email
you signed in with.

## The short version

- A **workspace** is your team's shared home — releases and connected tools all
  live inside it, and its name shows at the top of the sidebar.
- **Create** your own from the workspace menu, and **switch** between the ones you
  belong to from the same menu.
- Everyone is either an **owner** (can manage the team and the workspace) or a
  **member** (can work on releases). Any member can view the roster.
- Owners **change roles** and can **transfer ownership** by promoting a member and
  stepping down — a workspace always keeps **at least one owner**.
- **Owners invite** people with a shareable link tied to an email address; links
  expire after 14 days and can be revoked.
- Any member can **leave**, except the sole owner — promote a replacement first.
- An **owner deletes** a workspace from the danger zone; a workspace with other
  owners can't be deleted until they step down or leave. Deleting archives
  everything and can be **restored by support** if it was a mistake.

## Channel notes

Internal, v0.7.3. Grounded in the app's actual behavior:

- Slug for the in-app `<DocsLink>` deeplink is `workspaces`
  (`resources/js/docs/article-slugs.ts` → `PAGE_ARTICLE_SLUG['workspaces.members']`).
  Keep it stable.
- Create: `WorkspaceController@store`, `WorkspaceService::create` (creator becomes
  owner in one transaction; `DuplicateSlug` on collision).
- Switch: `WorkspaceSwitchController`; switcher offers Switch / Members & invites /
  Create workspace.
- Roles / transfer: `WorkspaceMemberController@updateMember` — last-owner guard
  under a workspace row lock (`'A workspace must keep at least one owner.'`).
- Remove: `@destroyMember` (owner-gated) → `MemberDepartureService::depart`
  (releases claims, detaches, re-resolves current workspace).
- Leave: `@leave` (member-authorized, not owner-gated); sole owner blocked with
  `'…Promote another member to owner before you leave.'`.
- Delete: `@destroy` (owner-gated 403) → `WorkspaceService::delete` — co-owner
  guard blocks when >1 owner (`'This workspace has other owners; they must step
  down or leave before it can be deleted.'`); soft-deletes + cascades archive to
  projects/releases, departs every member, revokes ghost/agent claims, expires
  pending invites.
- Recover: operator-only `php artisan srm:restore workspace <ulid>` (restores the
  workspace row + its trashed projects and releases). Not surfaced to users, hence
  "contact support" framing.
